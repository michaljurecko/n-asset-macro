<?php

namespace Webrouse\AssetMacro;

use Latte;
use Latte\Macros\MacroSet;
use Nette\Utils\Json;
use Nette\Utils\Strings;
use Nette\Utils\Validators;
use Webrouse\AssetMacro\Exceptions\AssetNotFoundException;
use Webrouse\AssetMacro\Exceptions\RevisionNotFound;
use Webrouse\AssetMacro\Exceptions\InvalidVariableException;
use Webrouse\AssetMacro\Exceptions\ManifestNotFoundException;


class AssetMacro extends MacroSet
{

	const CONFIG_PROVIDER = 'assetMacroConfig';

	private static $manifestCache = [];

	/**
	 * @param Latte\Compiler $compiler
	 */
	public static function install(Latte\Compiler $compiler)
	{
		$me = new self($compiler);
		$me->addMacro('asset', [$me, 'macroAsset']);
	}


	/**
	 * @param Latte\MacroNode $node
	 * @param Latte\PhpWriter $writer
	 * @return string
	 * @throws Latte\CompileException
	 */
	public function macroAsset(Latte\MacroNode $node, Latte\PhpWriter $writer)
	{
		$args = trim($node->args);

		// Validate arguments count
		$argsCount = $args === '' ? 0 : (substr_count($args, ',') + 1);
		if ($argsCount === 0) {
			throw new Latte\CompileException("Asset macro requires at least one argument.");
		}
		if ($argsCount > 3) {
			throw new Latte\CompileException("Asset macro must have no more than 3 arguments.");
		}

		return $writer->write(
			'echo %escape(' . self::class . '::resolveAssetPath(' .
			'%node.word, %node.array, $basePath, $this->global->' . self::CONFIG_PROVIDER . '))');
	}


	/**
	 * @param string $asset     Asset relative path
	 * @param array $args       Other macro arguments
	 * @param string $basePath  Base path
	 * @param array $config     Macro configuration
	 * @return string
	 */
	public static function resolveAssetPath($asset, array $args, $basePath, array $config)
	{
		list($path, $format, $need) = self::processArguments($asset, $args);
		$wwwDir = Utils::normalizePath($config['wwwDir']);
		$manifest = self::resolveManifest($path, $need, $wwwDir, $config);
		$revision = $manifest === NULL ? NULL : self::resolveRevision($manifest, $path, $need, $config);

		// Is revision only version (query parameter) or full path to asset?
		$revisionIsVersion = $revision === NULL || ! Strings::match($revision, '/[.\/]/');

		// Check if asset exists
		$ds = DIRECTORY_SEPARATOR;
		$filePath = $revisionIsVersion ?
			($wwwDir . $ds . $path) :
			($wwwDir . $ds . Utils::normalizePath($revision));
		if ( ! file_exists($filePath)) {
			Utils::throwError(
				new AssetNotFoundException(sprintf("Asset '%s' not found.", $filePath)),
				$config['missingAsset'],
				$need
			);
			return '';
		}

		// Format output
		return self::formatOutput($format, $basePath, $path, $revision, $revisionIsVersion);
	}


	/**
	 * @param string $format         Output format
	 * @param string $basePath       Base path
	 * @param string $path           Asset relative path
	 * @param string|null $revision  Asset revision (version or path to file)
	 * @param bool   $revisionIsVersion      Is revision only version or full path?
	 * @return string
	 */
	private static function formatOutput($format, $basePath, $path, $revision, $revisionIsVersion)
	{
		$revision = $revision ?: 'unknown';
		$path = $revisionIsVersion ? $path : $revision;

		return Strings::replace($format,
			'/%([^%]+)%/',
			function ($matches) use ($basePath, $format, $path, $revision, $revisionIsVersion) {
				switch ($matches[1]) {
					case 'raw':
						return $revision;
					case 'basePath':
						return $basePath;
					case 'path':
						return $path;
					case 'url':
						return $revisionIsVersion ?
							sprintf("%s/%s?v=%s", $basePath, $path, $revision) :
							sprintf("%s/%s", $basePath, $path);
					default:
						$msg = sprintf(
							"Asset macro: Invalid variable '%s' in format '%s'. " .
							"Use one of allowed variables: %%raw%%, %%basePath%%, %%path%%, %%url%%.",
							$matches[1],
							$format
						);
						throw new InvalidVariableException($msg);
				}
			});
	}


	/**
	 * @param string $asset  Asset path specified in macro
	 * @param array $args    Macro arguments
	 * @return array
	 */
	private static function processArguments($asset, array $args)
	{
		$format = isset($args['format']) ? $args['format'] : (isset($args[0]) ? $args[0] : '%url%');
		$need = isset($args['need']) ? $args['need'] : (isset($args[1]) ? $args[1] : TRUE);

		Validators::assert($asset, 'string', 'path');
		Validators::assert($format, 'string', 'format');
		Validators::assert($need, 'bool', 'need');

		$path = Utils::normalizePath($asset);

		return [$path, $format, $need];
	}


	/**
	 * @param string $asset   Asset path specified in macro
	 * @param bool $need      Fail if manifest doesn't exist?
	 * @param string $wwwDir  Public www dir
	 * @param array $config   Macro configuration
	 * @return null|array
	 */
	private static function resolveManifest($asset, $need, $wwwDir, array $config)
	{
		$manifest = $config['manifest'];

		// Asset revisions specified directly in configuration
		if (is_array($manifest)) {
			return $manifest;
		}

		// Path to JSON manifest
		if (is_string($manifest)) {
			if ( ! file_exists($manifest)) {
				Utils::throwError(
					new ManifestNotFoundException(sprintf("Manifest file not found: '%s'.", $manifest)),
					$config['missingManifest'],
					$need
				);
				return NULL;
			}
			return Json::decode(file_get_contents($manifest), Json::FORCE_ARRAY);
		}

		// Autodetect manifest path
		return self::autodetectManifest($asset, $wwwDir, $need, $config);
	}


	/**
	 * @param string $asset   Asset path specified in macro
	 * @param string $wwwDir  Public www dir
	 * @param bool $need      Fail if asset/manifest doesn't exist?
	 * @param array $config   Macro configuration
	 * @return null|array
	 */
	private static function autodetectManifest($asset, $wwwDir, $need, array $config)
	{
		// Finding a manifest begins in the asset directory
		$dir = $wwwDir . DIRECTORY_SEPARATOR . Utils::normalizePath(dirname($asset));

		// Autodetect manifest
		$autodetectPaths = $config['autodetect'];
		while (Strings::startsWith($dir, $wwwDir)) {
			foreach ($autodetectPaths as $path) {
				$path = $dir . DIRECTORY_SEPARATOR . $path;
				if (file_exists($path)) {
					return self::getManifest($path);
				}
			}

			$dir = dirname($dir); // go up
		}

		Utils::throwError(
			new ManifestNotFoundException(sprintf("Manifest not found in: %s.", implode(', ', $autodetectPaths))),
			$config['missingManifest'],
			$need
		);
		return NULL;
	}

	/**
	 * Get manifest content and cache it
	 * @param string $path
	 * @return array
	 */
	private static function getManifest($path) {
		if (!isset(self::$manifestCache[$path])) {
			self::$manifestCache[$path] = Json::decode(file_get_contents($path), Json::FORCE_ARRAY);
		}
		return self::$manifestCache[$path];
	}


	/**
	 * @param null|array $manifest  Array of revisions
	 * @param string $path          Asset path
	 * @param bool $need            Fail if revision doesn't exist?
	 * @param array $config         Macro configuration
	 * @return null|string
	 */
	private static function resolveRevision($manifest, $path, $need, array $config)
	{
		$revision = isset($manifest[$path]) ? $manifest[$path] : NULL;

		if ($revision === NULL) {
			Utils::throwError(
				new RevisionNotFound(sprintf("Revision for asset '%s' not found in manifest.", $path)),
				$config['missingRevision'],
				$need
			);
		}

		return $revision;
	}

}
