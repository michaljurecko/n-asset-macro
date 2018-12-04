<?php
declare(strict_types=1);

namespace Webrouse\AssetMacro;


use Nette\Http\IRequest;
use Nette\Utils\Strings;
use Webrouse\AssetMacro\Exceptions\InvalidVariableException;
use Webrouse\AssetMacro\Exceptions\ManifestNotFoundException;

class ManifestService
{
	/** @var Config */
	private $config;

	/** @var string */
	private $baseUrl;

	/** @var string */
	private $basePath;

	/** @var Manifest[] */
	private $manifestCache = [];


	public function __construct(Config $config, IRequest $httpRequest)
	{
		$this->config = $config;
		$url = $httpRequest->getUrl();
		$this->baseUrl = $url->getBaseUrl() . $this->config->getPublicPath();
		$this->basePath = $url->getBasePath() . $this->config->getPublicPath();
	}


	public function getConfig(): Config
	{
		return $this->config;
	}


	public function getManifest(string $assetPath = null, bool $needed = true): ?Manifest
	{
		// Manifest is specified by array in config
		if (($data = $this->config->getManifestAsArray()) !== null) {
			$key = '__array__';
			if (empty($this->manifestCache[$key])) {
				$this->manifestCache[$key] = new Manifest($this->config, null, $data);
			}
			return $this->manifestCache[$key];
		}

		// Load manifest from JSON file
		$path = $this->config->getManifestPath() ?? $this->autodetectManifest($assetPath, $needed);
		if (!$path) {
			return null;
		}

		if (!file_exists($path)) {
			Utils::throwError(
				new ManifestNotFoundException(sprintf("Manifest file not found: '%s'.", $path)),
				$this->config->getMissingManifestPolicy(),
				$needed
			);
			return null;
		}

		if (empty($this->manifestCache[$path])) {
			$this->manifestCache[$path] = new Manifest($this->config, $path);
		}

		return $this->manifestCache[$path];
	}


	public function getAsset(string $path, bool $needed = true): ?Asset
	{
		$manifest = $this->getManifest($path, $needed);
		return $manifest ? $manifest->getAsset($path, $needed) : $this->getAssetManifestNotFound($path);
	}


	public function formatOutput(Asset $asset, string $format = '%url%', bool $absolute = false): string
	{
		$base = $absolute ? $this->baseUrl : $this->basePath;

		return Strings::replace($format,
			'/%([^%]+)%/',
			function ($matches) use ($asset, $format, $base) {
				switch ($matches[1]) {
					case 'content':
						$content = file_get_contents($asset->getAbsolutePath());
						return $content ? trim($content) : '';
					case 'raw':
						return $asset->getRevision()->getRawValue();
					case 'base':
						return $base;
					case 'basePath':
						return $this->basePath;
					case 'baseUrl':
						return $this->baseUrl;
					case 'path':
						return $asset->getRelativePath();
					case 'url':
						return sprintf('%s%s', $base, $asset->getRelativeUrl());
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


	protected function getAssetManifestNotFound(string $path): Asset
	{
		$revision = new Revision($this->config, $path, null);
		return new Asset($revision);
	}


	private function autodetectManifest(string $assetPath = null, bool $needed = true): ?string
	{
		// Finding a manifest begins in the asset directory
		$dir = $assetPath ?
			$this->config->getAssetsPath() . DIRECTORY_SEPARATOR . Utils::normalizePath(dirname($assetPath)) :
			$this->config->getAssetsPath();

		// Autodetect manifest
		while (Strings::startsWith($dir, $this->config->getAssetsPath())) {
			foreach ($this->config->getManifestAutodetectPaths() as $path) {
				$path = $dir . DIRECTORY_SEPARATOR . $path;
				if (file_exists($path)) {
					return $path;
				}
			}

			$dir = dirname($dir); // go up ../
		}

		Utils::throwError(
			new ManifestNotFoundException(sprintf('Manifest not found in: %s.', implode(', ', $this->config->getManifestAutodetectPaths()))),
			$this->config->getMissingManifestPolicy(),
			$needed
		);

		return null;
	}
}
