<?php

namespace Webrouse\AssetMacro;

use Nette;
use Nette\Bridges\ApplicationLatte\ILatteFactory;
use Tester;
use Tester\TestCase;
use Tester\Assert;

include '../bootstrap.php';

class ExtensionTest extends TestCase
{
	/**
	 * Test load asset version from predefined autodetect paths (in all parent dirs)
	 */
	public function testExtensionManifestAutodetect() {
		$container = $this->createContainer("
extensions:
	assetMacro: Webrouse\\AssetMacro\\DI\\Extension
assetMacro:
	cache: false
		");

		$wwwDir = $container->parameters['wwwDir'];
		$this->createFile($wwwDir . '/assets/compiled/main.js');

		$paths = [
			'assets/compiled/assets.json',
			'assets/compiled/busters.json',
			'assets/compiled/versions.json',
			'assets/compiled/manifest.json',
			'assets/compiled/rev-manifest.json',
			'assets/assets.json',
			'assets/busters.json',
			'assets/versions.json',
			'assets/manifest.json',
			'assets/rev-manifest.json',
			'assets.json',
			'busters.json',
			'versions.json',
			'manifest.json',
			'rev-manifest.json',
		];

		foreach($paths as $path) {
			$path = $wwwDir . '/' . $path;
			$this->createFile($path, '{"assets/compiled/main.js": "8c48f58dfc7330c89c42550963c81546"}');

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
	public function testExtensionManifestFile() {
		$manifest = FIXTURES_DIR . '/www/versions-manifest.json';

		$container = $this->createContainer("
extensions:
	assetMacro: Webrouse\\AssetMacro\\DI\\Extension
assetMacro:
	cache: false
	manifest: $manifest
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
	public function testExtensionManifestArray() {
		$container = $this->createContainer("
extensions:
	assetMacro: Webrouse\\AssetMacro\\DI\\Extension
assetMacro:
	cache: false
	manifest:
		'assets/compiled/main.js': 8c48f58dfc7330c89c42550963c81546
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
	 * Test invalid value in missingAsset config key
	 * @throws \Webrouse\AssetMacro\Exceptions\UnexpectedValueException
	 */
	public function testExtensionInvalidMissingAsset() {
		$this->createContainer("
extensions:
	assetMacro: Webrouse\\AssetMacro\\DI\\Extension
assetMacro:
	cache: false
	missingAsset: abc
		");
	}

	/**
	 * Test invalid value in missingManifest config key
	 * @throws \Webrouse\AssetMacro\Exceptions\UnexpectedValueException
	 */
	public function testExtensionInvalidMissingManifest() {
		$this->createContainer("
extensions:
	assetMacro: Webrouse\\AssetMacro\\DI\\Extension
assetMacro:
	missingManifest: abc
		");
	}

	/**
	 * Test invalid value in missingRevision config key
	 * @throws \Webrouse\AssetMacro\Exceptions\UnexpectedValueException
	 */
	public function testExtensionInvalidMissingRevision() {
		$this->createContainer("
extensions:
	assetMacro: Webrouse\\AssetMacro\\DI\\Extension
assetMacro:
	cache: false
	missingRevision: abc
		");
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

(new ExtensionTest())->run();
