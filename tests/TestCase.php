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

		/** @var IRequest|Mock $httpRequest */
		$httpRequest = Mockery::mock(IRequest::class);
		$httpRequest->shouldReceive('getUrl')->andReturn($this->getFakeUrl());

		$config = new Config(array_merge(Extension::DEFAULTS, [
			'cache' => false,
			'assetsPath' => WWW_FIXTURES_DIR,
			'publicPath' => '/fixtures',
			'format' => '%url%',
		], $config));

		$service = new ManifestService($config, $httpRequest);

		$latte->addProvider(AssetMacro::MANIFEST_PROVIDER, $service);

		return $latte;
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
