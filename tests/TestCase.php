<?php
declare(strict_types=1);

namespace Webrouse\AssetMacro;

use Latte;
use Mockery;
use Mockery\Mock;
use Nette\Http\IRequest;
use Nette\Http\UrlScript;
use Tester\TestCase as TesterTestCase;
use Webrouse\AssetMacro\DI\Extension;

class TestCase extends TesterTestCase
{
	protected function createLatte(array $config = []): Latte\Engine
	{
		$latte = new Latte\Engine;
		$latte->setLoader(new Latte\Loaders\StringLoader);
		$latte->setTempDirectory(TEMP_DIR);
		$latte->onCompile[] = function (Latte\Engine $engine) {
			AssetMacro::install($engine->getCompiler());
		};

		$latte->addProvider(AssetMacro::MANIFEST_PROVIDER, $this->createService($config));

		return $latte;
	}


	protected function createService(array $config = []): ManifestService
	{
		/** @var IRequest|Mock $httpRequest */
		$httpRequest = Mockery::mock(IRequest::class);
		$httpRequest->shouldReceive('getUrl')->andReturn($this->getFakeUrl());

		$configService = new Config(array_merge(Extension::DEFAULTS, [
			'cache' => false,
			'assetsPath' => WWW_FIXTURES_DIR,
			'publicPath' => '/fixtures',
			'format' => '%url%',
		], $config));

		$formatter = new Formatter($configService, $httpRequest);

		return new ManifestService($configService, $formatter);
	}


	protected function getFakeUrl(): UrlScript
	{
		// Compatible with Nette 2.4 and 3.0
		$url = new UrlScript('http://www.example.com/base/path/index.php');
		$url->setScriptPath('/base/path/index.php');
		return $url;
	}


	protected function tearDown()
	{
		parent::tearDown();
		Mockery::close();
	}
}
