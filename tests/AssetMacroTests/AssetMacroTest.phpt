<?php

namespace Webrouse\AssetMacro;

use Latte;
use Tester\TestCase;
use Tester\Assert;

include '../bootstrap.php';

define('WWW_FIXTURES_DIR', $wwwDir = FIXTURES_DIR . '/www');

class AssetMacroTest extends TestCase {

	/**
	 * Test compilation of AssetMacro
	 */
	public function testCompile() {
		$latte = new Latte\Engine;
		$latte->setLoader(new Latte\Loaders\StringLoader);
		$latte->setTempDirectory(TEMP_DIR);
		$latte->onCompile[] = function (Latte\Engine $engine) {
			AssetMacro::install($engine->getCompiler());
		};

		// main.js
		$template1 = "{*template1*}{asset assets/compiled/main.js}";
		Assert::contains(
			AssetMacro::class . '::appendVersion("assets/compiled/main.js", $template->global->' . AssetMacro::VERSIONS_PROVIDER . ', $template->global->' .  AssetMacro::WWW_DIR_PROVIDER . ')',
			$latte->compile($template1)
		);

		// assets/compiled/main.css
		$template2 = "{*template2*}{asset assets/compiled/main.css}";
		Assert::contains(
			AssetMacro::class . '::appendVersion("assets/compiled/main.css", $template->global->' . AssetMacro::VERSIONS_PROVIDER . ', $template->global->' .  AssetMacro::WWW_DIR_PROVIDER . ')',
			$latte->compile($template2)
		);
	}

	/**
	 * Test render of AssetMacro
	 */
	public function testRender() {
		$latte = new Latte\Engine;
		$latte->setLoader(new Latte\Loaders\StringLoader);
		$latte->setTempDirectory(TEMP_DIR);
		$latte->addProvider(AssetMacro::VERSIONS_PROVIDER, WWW_FIXTURES_DIR . '/v.json');
		$latte->addProvider(AssetMacro::WWW_DIR_PROVIDER, WWW_FIXTURES_DIR);
		$latte->onCompile[] = function (Latte\Engine $engine) {
			AssetMacro::install($engine->getCompiler());
		};

		// main.js
		$template1 = "{*template1*}{asset assets/compiled/main.js}";
		Assert::same(
			'/base/path/assets/compiled/main.js?v=8c48f58dfc7330c89c42550963c81546',
			$latte->renderToString($template1, [
				'basePath' => '/base/path'
			])
		);

		// assets/compiled/main.css
		$template2 = "{*template2*}{asset assets/compiled/main.css}";
		Assert::same(
			'/base/path/assets/compiled/main.css?v=32ecae4b82916016edc74db0f5a11ceb',
			$latte->renderToString($template2, [
				'basePath' => '/base/path'
			])
		);
	}

	/**
	 * Test if macro throw exception if version file not exists
	 * @throws \Webrouse\AssetMacro\Exceptions\FileNotFoundException
	 */
	public function testMissingVersionFile() {
		$latte = new Latte\Engine;
		$latte->setLoader(new Latte\Loaders\StringLoader);
		$latte->setTempDirectory(TEMP_DIR);
		$latte->addProvider(AssetMacro::VERSIONS_PROVIDER, WWW_FIXTURES_DIR . '/version_invalid.json');
		$latte->addProvider(AssetMacro::WWW_DIR_PROVIDER, WWW_FIXTURES_DIR);
		$latte->onCompile[] = function (Latte\Engine $engine) {
			AssetMacro::install($engine->getCompiler());
		};

		$template = "{*template5*}{asset assets/compiled/main.js}";
		Assert::same('',$latte->renderToString($template, [
			'basePath' => '/base/path'
		]));
	}

	/**
	 * Test if is appended 'unknown' version to path, if record not found
	 */
	public function testMissingVersionRecord() {
		$latte = new Latte\Engine;
		$latte->setLoader(new Latte\Loaders\StringLoader);
		$latte->setTempDirectory(TEMP_DIR);
		$latte->addProvider(AssetMacro::VERSIONS_PROVIDER, WWW_FIXTURES_DIR . '/v.json');
		$latte->addProvider(AssetMacro::WWW_DIR_PROVIDER, WWW_FIXTURES_DIR);
		$latte->onCompile[] = function (Latte\Engine $engine) {
			AssetMacro::install($engine->getCompiler());
		};

		$template = "{*template7*}{asset assets/compiled/some.js}";
		Assert::same(
			'/base/path/assets/compiled/some.js?v=unknown',
			$latte->renderToString($template, [
				'basePath' => '/base/path'
			])
		);
	}

	/**
	 * Test if macro throw exception if www dir not exists
	 * @throws \Webrouse\AssetMacro\Exceptions\FileNotFoundException
	 */
	public function testWwwDirNotExists() {
		$latte = new Latte\Engine;
		$latte->setLoader(new Latte\Loaders\StringLoader);
		$latte->setTempDirectory(TEMP_DIR);
		$latte->addProvider(AssetMacro::VERSIONS_PROVIDER, WWW_FIXTURES_DIR . '/v.json');
		$latte->addProvider(AssetMacro::WWW_DIR_PROVIDER, '/invalid/www/dir');
		$latte->onCompile[] = function (Latte\Engine $engine) {
			AssetMacro::install($engine->getCompiler());
		};

		$template = "{*template7*}{asset assets/compiled/some.js}";
		$latte->renderToString($template, [
			'basePath' => '/base/path'
		]);
	}

	/**
	 * Test if macro throw exception if asset not exists
	 * @throws \Webrouse\AssetMacro\Exceptions\FileNotFoundException
	 */
	public function testAssetNotExists() {
		$latte = new Latte\Engine;
		$latte->setLoader(new Latte\Loaders\StringLoader);
		$latte->setTempDirectory(TEMP_DIR);
		$latte->addProvider(AssetMacro::VERSIONS_PROVIDER, WWW_FIXTURES_DIR . '/v.json');
		$latte->addProvider(AssetMacro::WWW_DIR_PROVIDER, WWW_FIXTURES_DIR);
		$latte->onCompile[] = function (Latte\Engine $engine) {
			AssetMacro::install($engine->getCompiler());
		};

		$template = "{*template7*}{asset assets/compiled/invalid.js}";
		$latte->renderToString($template, [
			'basePath' => '/base/path'
		]);
	}
}

run(new AssetMacroTest());
