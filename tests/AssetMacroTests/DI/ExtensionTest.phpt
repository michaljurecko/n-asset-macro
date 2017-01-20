<?php

namespace Webrouse\AssetMacro\DI;

use Nette;
use Nette\Bridges\ApplicationLatte\ILatteFactory;
use Tester;
use Tester\TestCase;
use Tester\Assert;

include '../../bootstrap.php';

class ExtensionTest extends TestCase
{
	/**
	 * Test if macro throw exception if version file not found (none of autodetect path exists)
	 * @throws \Webrouse\AssetMacro\Exceptions\FileNotFoundException
	 */
	public function testExtensionVersionsAutodetectNotFound() {
		$container = $this->createContainer("
extensions:
	assetMacro: Webrouse\\AssetMacro\\DI\\Extension
		");

		$wwwDir = $container->parameters['wwwDir'];
		$this->createFile($wwwDir . '/assets/compiled/main.js');

		/** @var ILatteFactory $latteFactory */
		$latteFactory = $container->getByType(ILatteFactory::class);
		$latte = $latteFactory->create();
		$latte->renderToString(FIXTURES_DIR . '/template1.latte', [
			'basePath' => '/base/path'
		]);
	}

	/**
	 * Test load asset version from predefined autodetect paths (in all parent dirs)
	 */
	public function testExtensionVersionsAutodetect() {
		$container = $this->createContainer("
extensions:
	assetMacro: Webrouse\\AssetMacro\\DI\\Extension
		");

		$wwwDir = $container->parameters['wwwDir'];
		$this->createFile($wwwDir . '/assets/compiled/main.js');

		$paths = [
			'assets/compiled/main.js.json',
			'assets/compiled/busters.json',
			'assets/compiled/versions.json',
			'assets/compiled/rev-manifest.json',
			'assets/busters.json',
			'assets/versions.json',
			'assets/rev-manifest.json',
			'busters.json',
			'versions.json',
			'rev-manifest.json',
		];

		foreach($paths as $path) {
			$path = $wwwDir . '/' . $path;
			$this->createFile($path, '{"main.js": "8c48f58dfc7330c89c42550963c81546"}');

			/** @var ILatteFactory $latteFactory */
			$latteFactory = $container->getByType(ILatteFactory::class);

			$latte = $latteFactory->create();
			Assert::same("/base/path/assets/compiled/main.js?v=8c48f58dfc7330c89c42550963c81546\n",
				$latte->renderToString(FIXTURES_DIR . '/template1.latte', [
					'basePath' => '/base/path'
				]));

			unlink($path);
		}
	}

	/**
	 * Test load asset version from file defined in configuration
	 */
	public function testExtensionVersionsFile() {
		$versionFile = FIXTURES_DIR . '/www/v.json';

		$container = $this->createContainer("
extensions:
	assetMacro: Webrouse\\AssetMacro\\DI\\Extension
assetMacro:
	versions: $versionFile
		");

		$wwwDir = $container->parameters['wwwDir'];
		$this->createFile($wwwDir . '/assets/compiled/main.js');

		/** @var ILatteFactory $latteFactory */
		$latteFactory = $container->getByType(ILatteFactory::class);

		$latte = $latteFactory->create();
		Assert::same("/base/path/assets/compiled/main.js?v=8c48f58dfc7330c89c42550963c81546\n",
			$latte->renderToString(FIXTURES_DIR . '/template1.latte', [
				'basePath' => '/base/path'
		]));
	}

	/**
	 * Test load asset version from array defined in configuration
	 */
	public function testExtensionVersionsArray() {
		$container = $this->createContainer("
extensions:
	assetMacro: Webrouse\\AssetMacro\\DI\\Extension
assetMacro:
	versions:
		'main.js': 8c48f58dfc7330c89c42550963c81546
		'assets/compiled/main.css': e9724c7164e33949129b964af7382dfa
		");

		$wwwDir = $container->parameters['wwwDir'];
		$this->createFile($wwwDir . '/assets/compiled/main.js');
		$this->createFile($wwwDir . '/assets/compiled/main.css');

		/** @var ILatteFactory $latteFactory */
		$latteFactory = $container->getByType(ILatteFactory::class);
		$latte = $latteFactory->create();
		Assert::same("/base/path/assets/compiled/main.js?v=8c48f58dfc7330c89c42550963c81546\n",
			$latte->renderToString(FIXTURES_DIR . '/template1.latte', [
				'basePath' => '/base/path'
			]));
		Assert::same("/base/path/assets/compiled/main.css?v=e9724c7164e33949129b964af7382dfa\n",
			$latte->renderToString(FIXTURES_DIR . '/template2.latte', [
				'basePath' => '/base/path'
			]));
	}

	/**
	 * @param string $configContent
	 * @return Nette\DI\Container
	 */
	protected function createContainer($configContent)
	{
		$hash = md5(TEMP_DIR . '_' . $configContent . '_' . Nette\Utils\Random::generate(10));
		$tempDir = TEMP_DIR . '/container_' . $hash;
		@mkdir($tempDir, 0777, TRUE);
		$appDir = $tempDir . '/app';
		@mkdir($appDir, 0777, TRUE);
		$wwwDir = $tempDir . '/www';
		@mkdir($wwwDir, 0777, TRUE);
		$config = new Nette\Configurator();
		$config->setTempDirectory($tempDir);
		$config->addParameters(['container' => ['class' => 'SystemContainer_' . $hash]]);
		$config->addParameters(['appDir' => $appDir, 'wwwDir' => $wwwDir]);
		$config->addConfig(TESTS_DIR . '/nette-reset.neon');
		$config->addConfig(Tester\FileMock::create($configContent, 'neon'));
		return $config->createContainer();
	}

	/**
	 * @param string $content
	 * @param string $path
	 */
	private function createFile($path, $content = 'content') {
		@mkdir(dirname($path), 0777, TRUE);
		file_put_contents(
			$path,
			$content
		);

	}
}

run(new ExtensionTest());
