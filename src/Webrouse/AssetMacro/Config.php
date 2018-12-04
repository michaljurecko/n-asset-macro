<?php
declare(strict_types=1);

namespace Webrouse\AssetMacro;


use Nette\Utils\FileSystem;
use Webrouse\AssetMacro\Exceptions\InvalidPathException;

class Config
{
	/** @var bool */
	private $cacheEnabled;

	/** @var array|null */
	private $manifestAsArray;

	/** @var string|null */
	private $manifestPath;

	/** @var array */
	private $manifestAutodetectPaths;

	/** @var string|null */
	private $assetsPath;

	/** @var string */
	private $publicPath;

	/** @var string */
	private $missingAssetPolicy;

	/** @var string */
	private $missingManifestPolicy;

	/** @var string */
	private $missingRevisionPolicy;

	/** @var string */
	private $defaultFormat;

	/** @var string */
	private $hash;


	public function __construct(array $config)
	{
		$this->cacheEnabled = $config['cache'];
		$this->manifestAsArray = is_array($config['manifest']) ? $config['manifest'] : null;
		$this->manifestPath = !$this->manifestAsArray ? $config['manifest'] : null;
		$this->manifestAutodetectPaths = $config['autodetect'];
		$this->assetsPath = $config['assetsPath'];
		$this->publicPath = $config['publicPath'];
		$this->missingAssetPolicy = $config['missingAsset'];
		$this->missingManifestPolicy = $config['missingManifest'];
		$this->missingRevisionPolicy = $config['missingRevision'];
		$this->defaultFormat = $config['format'];
		$this->hash = md5(serialize($config));

		// Normalize paths
		$this->assetsPath = Utils::normalizePath($this->assetsPath);
		$this->publicPath = Utils::normalizePath(trim($this->publicPath, '/'));
		$this->publicPath = $this->publicPath ? ($this->publicPath . '/') : '';

		// Assets path must be absolute
		if (!FileSystem::isAbsolute($config['assetsPath'])) {
			throw new InvalidPathException('Asset macro: assetsPath must be absolute.');
		}
	}


	public function isCacheEnabled(): bool
	{
		return $this->cacheEnabled;
	}


	public function getManifestAsArray(): ?array
	{
		return $this->manifestAsArray;
	}


	public function getManifestPath(): ?string
	{
		return $this->manifestPath;
	}


	public function getManifestAutodetectPaths(): array
	{
		return $this->manifestAutodetectPaths;
	}


	public function getAssetsPath(): string
	{
		return $this->assetsPath;
	}


	public function getPublicPath(): string
	{
		return $this->publicPath;
	}


	public function getMissingAssetPolicy(): string
	{
		return $this->missingAssetPolicy;
	}


	public function getMissingManifestPolicy(): string
	{
		return $this->missingManifestPolicy;
	}


	public function getMissingRevisionPolicy(): string
	{
		return $this->missingRevisionPolicy;
	}


	public function getDefaultFormat(): string
	{
		return $this->defaultFormat;
	}


	public function getHash(): string
	{
		return $this->hash;
	}
}
