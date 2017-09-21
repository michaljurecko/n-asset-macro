<?php

namespace Webrouse\AssetMacro;

use Latte;
use Latte\Macros\MacroSet;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use Nette\Utils\Json;
use Nette\Utils\Strings;
use Nette\Utils\Validators;
use Webrouse\AssetMacro\Exceptions\AssetNotFoundException;
use Webrouse\AssetMacro\Exceptions\RevisionNotFound;
use Webrouse\AssetMacro\Exceptions\InvalidVariableException;
use Webrouse\AssetMacro\Exceptions\ManifestNotFoundException;


class AssetMacro extends MacroSet
{

	/**
	 * Name of Latte provider of macro configuration
	 */
	const CONFIG_PROVIDER = 'assetMacroConfig';

	/**
	 * Memory cache for decoded JSON content of revisions manifests (path => content)
	 * @var array
	 */
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
		if ($node->modifiers && $node->modifiers != '|noescape') {
			throw new Latte\CompileException('Only \'noescape\' modifier is allowed in ' . $node->getNotation());
		}

		// Validate arguments count
		$args = trim($node->args);
		$argsCount = $args === '' ? 0 : (substr_count($args, ',') + 1);
		if ($argsCount === 0) {
			throw new Latte\CompileException("Asset macro requires at least one argument.");
		} else if ($argsCount > 3) {
			throw new Latte\CompileException("Asset macro must have no more than 3 arguments.");
		}

		return $writer->write(
			'echo ' . ($node->modifiers !== '|noescape' ? '%escape' : '') .
			'(' . self::class . '::generateOutput(' .
			'%node.word, ' .
			'%node.array, ' .
			'$basePath, ' .
			'$this->global->' . self::CONFIG_PROVIDER . ', ' .
			'isset($this->global->cacheStorage) ? $this->global->cacheStorage : null))');
	}

	/**
	 * @param string $asset      Asset relative path
	 * @param array $args        Other macro arguments
	 * @param string $basePath   Base path
	 * @param array $config      Macro configuration
	 * @param IStorage $storage  Cache storage
	 * @return string
	 */
	public static function getOutput($asset, array $args, $basePath, array $config, IStorage $storage = null)
	{
		$cacheKey = md5(implode(';', [$asset, $basePath, serialize($args), serialize($config)]));
		$cache = ($config['cache'] && $storage) ? new Cache($storage, 'Webrouse.AssetMacro') : null;

		// Load cached value
		if ($cache && ($output = $cache->load($cacheKey)) !== NULL) {
			return $output;
		}

		// Generate output and store value to cache
		$output = self::generateOutput($asset, $args, $basePath, $config);
		if ($cache) {
			$cache->save($cacheKey, $output);
		}

		return $output;
	}

	/**
	 * @param string $asset     Asset relative path
	 * @param array $args       Other macro arguments
	 * @param string $basePath  Base path
	 * @param array $config     Macro configuration
	 * @return string
	 */
	public static function generateOutput($asset, array $args, $basePath, array $config)
	{
		list($relativePath, $format, $needed) = self::processArguments($asset, $args);
		list($revision, $isVersion, $absolutePath) = self::getRevision($relativePath, $needed, $config);

		if (!file_exists($absolutePath)) {
			Utils::throwError(
				new AssetNotFoundException(sprintf("Asset '%s' not found.", $absolutePath)),
				$config['missingAsset'],
				$needed
			);
			return '';
		}

		return self::formatOutput($format, $absolutePath, $relativePath, $basePath, $revision, $isVersion);
	}


	/**
	 * @param string $asset  Asset path specified in macro
	 * @param array $args    Macro arguments
	 * @return array
	 */
	private static function processArguments($asset, array $args)
	{
		$format = isset($args['format']) ? $args['format'] : (isset($args[0]) ? $args[0] : '%url%');
		$needed = isset($args['need']) ? $args['need'] : (isset($args[1]) ? $args[1] : TRUE);

		Validators::assert($asset, 'string', 'path');
		Validators::assert($format, 'string', 'format');
		Validators::assert($needed, 'bool', 'need');

		$relativePath = Utils::normalizePath($asset);

		return [$relativePath, $format, $needed];
	}

	/**
	 * @param string $relativePath  Relative asset path
	 * @param string $needed        Fail if manifest doesn't exist?
	 * @param array $config         Macro configuration
	 * @return array
	 */
	private static function getRevision($relativePath, $needed, array $config)
	{
		$wwwDir = Utils::normalizePath($config['wwwDir']);
		$manifest = self::getManifest($relativePath, $needed, $wwwDir, $config);
		$revision = $manifest && isset($manifest[$relativePath]) ? $manifest[$relativePath] : null;

		// Throw error if revision not found in manifest
		if ($manifest && $revision === null) {
			Utils::throwError(
				new RevisionNotFound(sprintf("Revision for asset '%s' not found in manifest.", $relativePath)),
				$config['missingRevision'],
				$needed
			);
		}

		// Is revision only version (query parameter) or full path to asset?
		$isVersion = $revision === null || !Strings::match($revision, '/[.\/]/');

		// Check if asset exists
		$filePath = $isVersion ?
			($wwwDir . DIRECTORY_SEPARATOR . $relativePath) :
			($wwwDir . DIRECTORY_SEPARATOR . Utils::normalizePath($revision));

		return [$revision, $isVersion, $filePath];
	}


	/**
	 * @param string $asset   Asset path specified in macro
	 * @param bool $needed    Fail if manifest doesn't exist?
	 * @param string $wwwDir  Public www dir
	 * @param array $config   Macro configuration
	 * @return null|array
	 */
	private static function getManifest($asset, $needed, $wwwDir, array $config)
	{
		$manifest = $config['manifest'];

		// Asset revisions specified directly in configuration
		if (is_array($manifest)) {
			return $manifest;
		}

		// Path to JSON manifest
		if (is_string($manifest)) {
			if (!file_exists($manifest)) {
				Utils::throwError(
					new ManifestNotFoundException(sprintf("Manifest file not found: '%s'.", $manifest)),
					$config['missingManifest'],
					$needed
				);
				return null;
			}
			return Json::decode(file_get_contents($manifest), Json::FORCE_ARRAY);
		}

		// Autodetect manifest path
		return self::autodetectManifest($asset, $wwwDir, $needed, $config);
	}


	/**
	 * @param string $asset   Asset path specified in macro
	 * @param string $wwwDir  Public www dir
	 * @param bool $needed    Fail if asset/manifest doesn't exist?
	 * @param array $config   Macro configuration
	 * @return null|array
	 */
	private static function autodetectManifest($asset, $wwwDir, $needed, array $config)
	{
		// Finding a manifest begins in the asset directory
		$dir = $wwwDir . DIRECTORY_SEPARATOR . Utils::normalizePath(dirname($asset));

		// Autodetect manifest
		$autodetectPaths = $config['autodetect'];
		while (Strings::startsWith($dir, $wwwDir)) {
			foreach ($autodetectPaths as $path) {
				$path = $dir . DIRECTORY_SEPARATOR . $path;
				if (file_exists($path)) {
					if (!isset(self::$manifestCache[$path])) {
						self::$manifestCache[$path] = Json::decode(file_get_contents($path), Json::FORCE_ARRAY);
					}
					return self::$manifestCache[$path];
				}
			}

			$dir = dirname($dir); // go up ../
		}

		Utils::throwError(
			new ManifestNotFoundException(sprintf("Manifest not found in: %s.", implode(', ', $autodetectPaths))),
			$config['missingManifest'],
			$needed
		);
		return null;
	}

	/**
	 * @param string $format           Output format
	 * @param string $absolutePath     Absolute asset path
	 * @param string $relativePath     Asset relative path
	 * @param string $basePath         Base path
	 * @param string|null $revision    Asset revision (version or path to file)
	 * @param bool $revisionIsVersion  Is revision only version or full path?
	 * @return string
	 */
	private static function formatOutput($format, $absolutePath, $relativePath, $basePath, $revision, $revisionIsVersion)
	{
		$revision = $revision ?: 'unknown';
		$relativePath = $revisionIsVersion ? $relativePath : $revision;

		return Strings::replace($format,
			'/%([^%]+)%/',
			function ($matches) use ($format, $absolutePath, $relativePath, $basePath, $revision, $revisionIsVersion) {
				switch ($matches[1]) {
					case 'content':
						return trim(file_get_contents($absolutePath));
					case 'raw':
						return $revision;
					case 'basePath':
						return $basePath;
					case 'path':
						return $relativePath;
					case 'url':
						return $revisionIsVersion ?
							sprintf("%s/%s?v=%s", $basePath, $relativePath, $revision) :
							sprintf("%s/%s", $basePath, $relativePath);
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
}
