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
		$latte = $this->createLatte([
			'manifest' => null,
			'autodetect' => [
				'versions-manifest.json',
			],
			'missingAsset' => 'ignore',
			'missingManifest' => 'exception',
			'missingRevision' => 'ignore',
		]);

		Assert::same(
			'/base/path/fixtures/assets/compiled/main.js?v=8c48f58dfc7330c89c42550963c81546',
			$latte->renderToString('{asset "assets/compiled/main.js"}')
		);
	}


	/**
	 * Test missing manifest in autodetection
	 * @throws \Webrouse\AssetMacro\Exceptions\ManifestNotFoundException
	 */
	public function testAutodetectMissingManifestException()
	{
		$latte = $this->createLatte([
			'manifest' => null,
			'autodetect' => [
				'X.json',
			],
			'missingAsset' => 'ignore',
			'missingManifest' => 'exception',
			'missingRevision' => 'ignore',
		]);

		$template = '{asset "assets/compiled/main.js"}';
		$latte->renderToString($template);
	}


	/**
	 * Test missing manifest in autodetection
	 */
	public function testAutodetectMissingManifestIgnore()
	{
		$latte = $this->createLatte([
			'manifest' => null,
			'autodetect' => [
				'X.json',
			],
			'missingAsset' => 'ignore',
			'missingManifest' => 'notice',
			'missingRevision' => 'ignore',
		]);

		$template = '{asset "assets/compiled/main.js"}';
		Assert::error(function () use ($latte, $template) {
			$latte->renderToString($template);
		}, E_USER_NOTICE);

		error_reporting(E_ERROR | E_PARSE);
		Assert::equal(
			'/base/path/fixtures/assets/compiled/main.js?v=unknown',
			$latte->renderToString($template)
		);
	}
}

(new ManifestTest())->run();
