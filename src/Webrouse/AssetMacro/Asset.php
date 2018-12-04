<?php
declare(strict_types=1);

namespace Webrouse\AssetMacro;


class Asset
{
	/** @var Revision */
	private $revision;


	public function __construct(Revision $revision)
	{
		$this->revision = $revision;
	}


	public function getRevision(): Revision
	{
		return $this->revision;
	}


	public function getAssetPath(): string
	{
		return $this->revision->getAssetPath();
	}


	public function getRelativePath(): string
	{
		return $this->revision->getRelativePath();
	}


	public function getAbsolutePath(): string
	{
		return $this->revision->getAbsolutePath();
	}


	public function getRelativeUrl(): string
	{
		return $this->revision->getRelativeUrl();
	}
}
