<?php
declare(strict_types=1);

namespace Webrouse\AssetMacro;

use Tester\Assert;

include '../bootstrap.php';

/**
 * @testCase
 */
class ManifestTest extends TestCase
{

	/**
	 * Test autodetect of revision manifest
	 */
	public function testAutodetectManifest()
	{
		$latte = TestUtils::createLatte();
		$latte->addProvider(AssetMacro::CONFIG_PROVIDER, [
			'cache' => false,
			'manifest' => null,
			'autodetect' => [
				'versions-manifest.json',
			],
			'wwwDir' => WWW_FIXTURES_DIR,
			'missingAsset' => 'ignore',
			'missingManifest' => 'exception',
			'missingRevision' => 'ignore',
			'format' => '%url%',
		]);

		$template = '{asset "assets/compiled/main.js"}';
		Assert::same(
			'/base/path/assets/compiled/main.js?v=8c48f58dfc7330c89c42550963c81546',
			$latte->renderToString($template, self::LATTE_VARS)
		);
	}


	/**
	 * Test missing manifest in autodetection
	 * @throws \Webrouse\AssetMacro\Exceptions\ManifestNotFoundException
	 */
	public function testAutodetectMissingManifestException()
	{
		$latte = TestUtils::createLatte();
		$latte->addProvider(AssetMacro::CONFIG_PROVIDER, [
			'cache' => false,
			'manifest' => null,
			'autodetect' => [
				'X.json',
			],
			'wwwDir' => WWW_FIXTURES_DIR,
			'missingAsset' => 'ignore',
			'missingManifest' => 'exception',
			'missingRevision' => 'ignore',
			'format' => '%url%',
		]);

		$template = '{asset "assets/compiled/main.js"}';
		$latte->renderToString($template, self::LATTE_VARS);
	}


	/**
	 * Test missing manifest in autodetection
	 */
	public function testAutodetectMissingManifestIgnore()
	{
		$latte = TestUtils::createLatte();
		$latte->addProvider(AssetMacro::CONFIG_PROVIDER, [
			'cache' => false,
			'manifest' => null,
			'autodetect' => [
				'X.json',
			],
			'wwwDir' => WWW_FIXTURES_DIR,
			'missingAsset' => 'ignore',
			'missingManifest' => 'notice',
			'missingRevision' => 'ignore',
			'format' => '%url%',
		]);

		$template = '{asset "assets/compiled/main.js"}';
		Assert::error(function () use ($latte, $template) {
			$latte->renderToString($template, self::LATTE_VARS);
		}, E_USER_NOTICE);

		error_reporting(E_ERROR | E_PARSE);
		Assert::equal(
			'/base/path/assets/compiled/main.js?v=unknown',
			$latte->renderToString($template, self::LATTE_VARS)
		);
	}
}

(new ManifestTest())->run();
