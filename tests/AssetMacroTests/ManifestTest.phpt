<?php

namespace Webrouse\AssetMacro;

use Tester\TestCase;
use Tester\Assert;

include '../bootstrap.php';

/**
 * @testCase
 */
class ManifestTest extends TestCase {

	/**
	 * Test autodetect of revision manifest
	 */
	public function testAutodetectManifest() {
		$latte = TestUtils::createLatte();
		$latte->addProvider(AssetMacro::CONFIG_PROVIDER, [
            'cache' => false,
			'manifest' => NULL,
			'autodetect' => [
				'versions-manifest.json',
			],
			'wwwDir' => WWW_FIXTURES_DIR,
			'missingAsset' => 'ignore',
			'missingManifest' => 'exception',
			'missingRevision' => 'ignore',
		]);

		$template = '{asset "assets/compiled/main.js"}';
		Assert::same(
			'/base/path/assets/compiled/main.js?v=8c48f58dfc7330c89c42550963c81546',
			$latte->renderToString($template, [
				'basePath' => '/base/path'
			])
		);
	}

	/**
	 * Test missing manifest in autodetection
	 * @throws \Webrouse\AssetMacro\Exceptions\ManifestNotFoundException
	 */
	public function testAutodetectMissingManifestException() {
		$latte = TestUtils::createLatte();
		$latte->addProvider(AssetMacro::CONFIG_PROVIDER, [
            'cache' => false,
			'manifest' => NULL,
			'autodetect' => [
				'X.json',
			],
			'wwwDir' => WWW_FIXTURES_DIR,
			'missingAsset' => 'ignore',
			'missingManifest' => 'exception',
			'missingRevision' => 'ignore',
		]);

		$template = '{asset "assets/compiled/main.js"}';
		$latte->renderToString($template, [
			'basePath' => '/base/path'
		]);
	}

	/**
	 * Test missing manifest in autodetection
	 */
	public function testAutodetectMissingManifestIgnore() {
		$latte = TestUtils::createLatte();
		$latte->addProvider(AssetMacro::CONFIG_PROVIDER, [
            'cache' => false,
			'manifest' => NULL,
			'autodetect' => [
				'X.json',
			],
			'wwwDir' => WWW_FIXTURES_DIR,
			'missingAsset' => 'ignore',
			'missingManifest' => 'notice',
			'missingRevision' => 'ignore',
		]);

		$template = '{asset "assets/compiled/main.js"}';
		Assert::error(function() use ($latte, $template) {
			$latte->renderToString($template, [
				'basePath' => '/base/path'
			]);
		}, E_USER_NOTICE);

		error_reporting(E_ERROR | E_PARSE);
		Assert::equal(
			'/base/path/assets/compiled/main.js?v=unknown',
			$latte->renderToString($template, [
				'basePath' => '/base/path'
			])
		);
	}
}

(new ManifestTest())->run();
