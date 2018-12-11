<?php
declare(strict_types=1);

namespace Webrouse\AssetMacro;

use Nette\Http\IRequest;
use Nette\Utils\Strings;
use Webrouse\AssetMacro\Exceptions\InvalidVariableException;



class Formatter implements IFormatter
{
	/** @var Config */
	private $config;

	/** @var string */
	private $baseUrl;

	/** @var string */
	private $basePath;


	public function __construct(Config $config, IRequest $httpRequest)
	{
		$this->config = $config;
		$url = $httpRequest->getUrl();
		$this->baseUrl = $url->getBaseUrl() . $this->config->getPublicPath();
		$this->basePath = $url->getBasePath() . $this->config->getPublicPath();
	}


	public function format(Asset $asset, string $format = '%url%', bool $absolute = false): string
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
}
