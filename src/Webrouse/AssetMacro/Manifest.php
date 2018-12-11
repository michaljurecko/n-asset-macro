<?php
declare(strict_types=1);

namespace Webrouse\AssetMacro;


use Nette\Utils\Json;
use Nette\Utils\JsonException;
use Nette\Utils\Strings;
use Webrouse\AssetMacro\Exceptions\AssetNotFoundException;
use Webrouse\AssetMacro\Exceptions\ManifestJsonException;
use Webrouse\AssetMacro\Exceptions\RevisionNotFound;

class Manifest
{
	/** @var Config */
	private $config;

	/** @var string|null */
	private $path;

	/** @var array */
	private $data = [];


	public function __construct(Config $config, ?string $path, array $data = null)
	{
		$this->config = $config;
		$this->path = $path;

		if ($data) {
			$raw = $data;
		} else {
			assert(file_exists($path));
			try {
				$raw = Json::decode((string) file_get_contents($path), Json::FORCE_ARRAY);
			} catch (JsonException $e) {
				throw new ManifestJsonException('Invalid JSON in manifest.', 0, $e);
			}
		}

		// Strip optional leading / from source and target path (key and value)
		foreach ($raw as $key => $value) {
			$key = ltrim((string) $key, '/');
			$value = ltrim((string) $value, '/');
			$this->data[$key] = $value;
		}
	}


	/**
	 * @param null|string|callable $filter regexp pattern or callable
	 * @param bool $needed
	 * @return array|Asset[]
	 */
	public function getAll($filter = null, $needed = true): array
	{
		assert($filter === null || is_string($filter) || is_callable($filter));

		$out = [];
		foreach (array_keys($this->data) as $asset) {
			if (is_string($filter)) {
				if (!Strings::match($asset, $filter)) {
					continue;
				}
			} elseif (is_callable($filter)) {
				if (!$filter($asset)) {
					continue;
				}
			}
			$out[$asset] = $this->getAsset($asset, $needed);
		}

		return $out;
	}


	public function getAsset(string $path, bool $needed = true): Asset
	{
		$revision = self::getRevision($path, $needed);

		if (!file_exists($revision->getAbsolutePath())) {
			Utils::throwError(
				new AssetNotFoundException(sprintf("Asset '%s' not found.", $revision->getAbsolutePath())),
				$this->config->getMissingAssetPolicy(),
				$needed
			);
		}

		return new Asset($revision);
	}


	public function getRevision(string $path, bool $needed = true): Revision
	{
		// Strip optional leading / from path
		$path = Utils::normalizePath(ltrim($path, '/'));
		$revision = isset($this->data[$path]) ? $this->data[$path] : null;

		// Throw error if revision not found in manifest
		if ($revision === null) {
			Utils::throwError(
				new RevisionNotFound(sprintf("Revision for asset '%s' not found in manifest %s.", $path, $this->path)),
				$this->config->getMissingRevisionPolicy(),
				$needed
			);
		}

		return new Revision($this->config, $path, $revision);
	}
}
