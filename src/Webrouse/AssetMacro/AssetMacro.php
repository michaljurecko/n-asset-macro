<?php
declare(strict_types=1);

namespace Webrouse\AssetMacro;

use Latte;
use Latte\Macros\MacroSet;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use Nette\Utils\Json;
use Nette\Utils\JsonException;
use Nette\Utils\Strings;
use Nette\Utils\Validators;
use Webrouse\AssetMacro\Exceptions\AssetNotFoundException;
use Webrouse\AssetMacro\Exceptions\InvalidVariableException;
use Webrouse\AssetMacro\Exceptions\ManifestJsonException;
use Webrouse\AssetMacro\Exceptions\ManifestNotFoundException;
use Webrouse\AssetMacro\Exceptions\RevisionNotFound;


class AssetMacro extends MacroSet
{

	/**
	 * Name of Latte provider of macro configuration
	 */
	public const CONFIG_PROVIDER = 'assetMacroConfig';

	/**
	 * Memory cache for decoded JSON content of revisions manifests (path => content)
	 * @var array
	 */
	private static $manifestCache = [];


	public static function install(Latte\Compiler $compiler): void
	{
		$me = new self($compiler);
		$me->addMacro('asset', [$me, 'macroAsset']);
	}


	public function macroAsset(Latte\MacroNode $node, Latte\PhpWriter $writer): string
	{
		if ($node->modifiers && $node->modifiers !== '|noescape') {
			throw new Latte\CompileException('Only \'noescape\' modifier is allowed in ' . $node->getNotation());
		}

		// Validate arguments count
		$args = trim($node->args);
		$argsCount = $args === '' ? 0 : (substr_count($args, ',') + 1);
		if ($argsCount === 0) {
			throw new Latte\CompileException('Asset macro requires at least one argument.');
		} elseif ($argsCount > 3) {
			throw new Latte\CompileException('Asset macro must have no more than 3 arguments.');
		}

		return $writer->write(
			'echo ' . ($node->modifiers !== '|noescape' ? '%escape' : '') .
			'(' . self::class . '::getOutput(' .
			'%node.word, ' .
			'%node.array, ' .
			'$basePath, ' .
			'$this->global->' . self::CONFIG_PROVIDER . ', ' .
			'isset($this->global->cacheStorage) ? $this->global->cacheStorage : null))');
	}


	public static function getOutput(string $asset, array $args, string $basePath, array $config, IStorage $storage = null): string
	{
		$cacheKey = md5(implode(';', [$asset, $basePath, serialize($args), serialize($config)]));
		$cache = ($config['cache'] && $storage) ? new Cache($storage, 'Webrouse.AssetMacro') : null;

		// Load cached value
		if ($cache && ($output = $cache->load($cacheKey)) !== null) {
			return $output;
		}

		// Generate output and store value to cache
		$output = self::generateOutput($asset, $args, $basePath, $config);
		if ($cache) {
			$cache->save($cacheKey, $output);
		}

		return $output;
	}


	public static function generateOutput(string $asset, array $args, string $basePath, array $config): string
	{
		[$relativePath, $format, $needed] = self::processArguments($asset, $args);
		[$revision, $isVersion, $absolutePath] = self::getRevision($relativePath, $needed, $config);

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


	private static function processArguments(string $asset, array $args): array
	{
		$format = $args['format'] ?? ($args[0] ?? '%url%');
		$needed = $args['need'] ?? ($args[1] ?? true);

		Validators::assert($asset, 'string', 'path');
		Validators::assert($format, 'string', 'format');
		Validators::assert($needed, 'bool', 'need');

		$relativePath = Utils::normalizePath($asset);

		return [$relativePath, $format, $needed];
	}


	private static function getRevision(string $relativePath, bool $needed, array $config): array
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

		// Strip optional leading / from asset path
		$revision = $revision ? ltrim($revision, '/') : null;

		// Check if asset exists
		$filePath = $wwwDir . DIRECTORY_SEPARATOR . ($isVersion ? $relativePath : Utils::normalizePath($revision));

		return [$revision, $isVersion, $filePath];
	}


	private static function getManifest(string $asset, bool $needed, string $wwwDir, array $config): ?array
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

			try {
				return Json::decode((string) file_get_contents($manifest), Json::FORCE_ARRAY);
			} catch (JsonException $e) {
				throw new ManifestJsonException('Invalid JSON in manifest.', 0, $e);
			}
		}

		// Autodetect manifest path
		return self::autodetectManifest($asset, $wwwDir, $needed, $config);
	}


	private static function autodetectManifest(string $asset, string $wwwDir, bool $needed, array $config): ?array
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
						self::$manifestCache[$path] = Json::decode((string) file_get_contents($path), Json::FORCE_ARRAY);
					}
					return self::$manifestCache[$path];
				}
			}

			$dir = dirname($dir); // go up ../
		}

		Utils::throwError(
			new ManifestNotFoundException(sprintf('Manifest not found in: %s.', implode(', ', $autodetectPaths))),
			$config['missingManifest'],
			$needed
		);

		return null;
	}


	private static function formatOutput(string $format, string $absolutePath, string $relativePath, string $basePath, ?string $revision, bool $revisionIsVersion): string
	{
		$revision = $revision ?: 'unknown';
		$relativePath = $revisionIsVersion ? $relativePath : $revision;

		return Strings::replace($format,
			'/%([^%]+)%/',
			function ($matches) use ($format, $absolutePath, $relativePath, $basePath, $revision, $revisionIsVersion) {
				switch ($matches[1]) {
					case 'content':
						$content = file_get_contents($absolutePath);
						return $content ? trim($content) : '';
					case 'raw':
						return $revision;
					case 'basePath':
						return $basePath;
					case 'path':
						return $relativePath;
					case 'url':
						return $revisionIsVersion ?
							sprintf('%s/%s?v=%s', $basePath, $relativePath, $revision) :
							sprintf('%s/%s', $basePath, $relativePath);
					default:
						$msg = sprintf(
							"Asset macro: Invalid variable '%s' in format '%s'. " .
							'Use one of allowed variables: %%raw%%, %%basePath%%, %%path%%, %%url%%.',
							$matches[1],
							$format
						);
						throw new InvalidVariableException($msg);
				}
			});
	}
}
