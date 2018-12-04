<?php
declare(strict_types=1);

namespace Webrouse\AssetMacro;


use Nette\Utils\Strings;

class Revision
{
	/** @var Config */
	private $config;

	/** @var string */
	private $assetPath;

	/** @var string */
	private $rawValue;

	/** @var bool */
	private $isVersion;

	/** @var string */
	private $relativePath;

	/** @var string */
	private $absolutePath;

	/** @var string */
	private $relativeUrl;


	public function __construct(Config $config, string $assetPath, ?string $revision)
	{
		$this->config = $config;
		$this->assetPath = $assetPath;
		$this->rawValue = $revision ?: 'unknown';

		// Is revision only version (query parameter) or full path to asset?
		$this->isVersion = $revision === null || !Strings::match((string) $revision, '/[.\/]/');

		$this->relativePath = ($this->isVersion ? $this->assetPath : Utils::normalizePath($revision));
		$this->absolutePath = $this->config->getAssetsPath() . DIRECTORY_SEPARATOR . $this->relativePath;
		$this->relativeUrl = $this->isVersion ? ($this->assetPath . '?v=' . $this->rawValue) : $revision;
	}


	public function getRawValue(): string
	{
		return $this->rawValue;
	}


	public function isVersionHash(): bool
	{
		return $this->isVersion;
	}


	public function getAssetPath(): string
	{
		return $this->assetPath;
	}


	public function getRelativePath(): string
	{
		return $this->relativePath;
	}


	public function getAbsolutePath(): string
	{
		return $this->absolutePath;
	}


	public function getRelativeUrl(): string
	{
		return $this->relativeUrl;
	}
}
