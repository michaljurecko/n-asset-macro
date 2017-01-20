<?php

namespace Webrouse\AssetMacro;

use Latte;
use Latte\Macros\MacroSet;
use Nette\Utils\Json;
use Nette\Utils\Strings;
use Webrouse\AssetMacro\Exceptions\DirNotFoundException;
use Webrouse\AssetMacro\Exceptions\FileNotFoundException;


class AssetMacro extends MacroSet
{

	const VERSIONS_AUTODETECT = NULL;

	const VERSIONS_AUTODETECT_PATHS = [
		'busters.json',
		'versions.json',
		'rev-manifest.json'
	];

	const VERSIONS_PROVIDER = 'assetMacroVersions';

	const WWW_DIR_PROVIDER = 'assetMacroWwwDir';

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
	 */
	public function macroAsset(Latte\MacroNode $node, Latte\PhpWriter $writer)
	{
		$content =
			self::class .
			'::appendVersion(' .
			'%node.word, ' .
			'$template->global->' . self::VERSIONS_PROVIDER . ', ' .
			'$template->global->' . self::WWW_DIR_PROVIDER  .
			')';
		return $writer->write('echo $basePath . "/" . %escape(' . $content .')');
	}


	/**
	 * Append version (from JSON file or versions array) to asset relative path.
	 *
	 * @param string $relativePath
	 * @param string|array $assetVersions
	 * @param string $wwwDir
	 * @return string
	 */
	public static function appendVersion($relativePath, $assetVersions, $wwwDir)
	{
		$relativePath = ltrim($relativePath, '\\/');

		if (($normalizedWwwDir = realpath($wwwDir)) === FALSE) {
			throw new DirNotFoundException(sprintf("Www dir '%s' not found.", $wwwDir));
		}
		if (($absolutePath = realpath($normalizedWwwDir . DIRECTORY_SEPARATOR . $relativePath)) === FALSE) {
			throw new FileNotFoundException(sprintf("Asset '%s' not found.", $relativePath));
		}

		if ($assetVersions === self::VERSIONS_AUTODETECT) {
			$assetVersions = self::autodetectVersionsFile($absolutePath, $normalizedWwwDir);
		}

		return $relativePath . '?v=' . self::getAssetVersion($assetVersions, $absolutePath);
	}


	/**
	 * Get asset version hash.
	 * If the record can not be found, then version is 'unknown'.
	 *
	 * @param string|array $assetsVersions
	 * @param string $absolutePath
	 * @return mixed|string
	 */
	private static function getAssetVersion($assetsVersions, $absolutePath)
	{
		// Versions can be array or path to JSON file
		if ( ! is_array($assetsVersions)) {
			if ( ! file_exists($assetsVersions)) {
				throw new FileNotFoundException(sprintf("Asset versions file not found: '%s'.", $assetsVersions));
			}
			$assetsVersions = Json::decode(file_get_contents($assetsVersions), Json::FORCE_ARRAY);
		}

		foreach($assetsVersions as $path => $hash) {
			// Test if path from version file (may be relative) is in asset path
			if (Strings::endsWith($absolutePath, $path)) {
				return $hash;
			}
		}

		return 'unknown';
	}


	/**
	 * Autodetect version file path.
	 * It searches the asset directory and all parent directories up to www dir for files:
	 * - busters.json
	 * - versions.json
	 * - rev-manifest.json
	 * It is also possible to use asset name with the extension JSON (eg. some/path/main.js.json).
	 *
	 * @param string $absolutePath
	 * @param string $wwwDir
	 * @return mixed|string
	 */
	private static function autodetectVersionsFile($absolutePath, $wwwDir)
	{
		// First, test if there asset filename + '.json'
		if (file_exists($absolutePath . '.json')) {
			return $absolutePath . '.json';
		}

		// Iterate over parent directories (stop in www dir)
		$dir = dirname($absolutePath);
		while(Strings::startsWith($dir, $wwwDir)) {
			foreach(self::VERSIONS_AUTODETECT_PATHS as $path) {
				$path = $dir . DIRECTORY_SEPARATOR . $path;
				if (file_exists($path)) {
					return $path;
				}
			}

			// Get parent directory
			$dir = dirname($dir);
		}

		throw new FileNotFoundException(
			sprintf("None of the version files (%s) can be found in '%s' and parent directories up to www dir '%s'" .
				"Create one of these files or set 'assetMacro.versions' in configuration.",
				implode(', ', self::VERSIONS_AUTODETECT_PATHS),
				dirname($absolutePath),
				$wwwDir
			)
		);
	}

}
