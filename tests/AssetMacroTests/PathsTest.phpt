<?php

namespace Webrouse\AssetMacro;

use Latte;
use Tester\TestCase;
use Tester\Assert;

include '../bootstrap.php';

/**
 * * Test asset macro with revision manifest file with format ASSET => PATH
 * @testCase
 */
class PathsTest extends TestCase {

	/**
	 * Test asset macro render
	 */
	public function testRender() {
		$latte = TestUtils::createLatte();
		$latte->addProvider(AssetMacro::CONFIG_PROVIDER, [
			'manifest' => WWW_FIXTURES_DIR . '/paths-manifest.json',
			'autodetect' => [],
			'wwwDir' => WWW_FIXTURES_DIR,
			'missingAsset' => 'notice',
			'missingManifest' => 'notice',
			'missingRevision' => 'ignore',
		]);

		$template = '{asset "assets/compiled/main.js"}';

		Assert::same(
			'/base/path/assets/compiled/main.fc730c89c4255.js',
			$latte->renderToString($template, [
				'basePath' => '/base/path'
			])
		);
	}

    /**
     * Test if asset macro escapes parh correctly
     */
    public function testEscape() {
        $latte = TestUtils::createLatte();
        $latte->addProvider(AssetMacro::CONFIG_PROVIDER, [
            'manifest' => WWW_FIXTURES_DIR . '/paths-manifest.json',
            'autodetect' => [],
            'wwwDir' => WWW_FIXTURES_DIR,
            'missingAsset' => 'notice',
            'missingManifest' => 'notice',
            'missingRevision' => 'ignore',
        ]);

        $template = '<script src="{asset "assets/compiled/escape.js"}"></script>';

        Assert::same(
            '<script src="/base/path/assets/compiled/main.&quot;escape&quot;.js"></script>',
            $latte->renderToString($template, [
                'basePath' => '/base/path'
            ])
        );
    }

	/**
	 * Test asset macro if first argument has invalid type
	 * @throws \Nette\Utils\AssertionException
	 */
	public function testInvalidTypeAssetArgument() {
		$latte = TestUtils::createLatte();
		$latte->addProvider(AssetMacro::CONFIG_PROVIDER, [
			'manifest' => WWW_FIXTURES_DIR . '/paths-manifest.json',
			'autodetect' => [],
			'wwwDir' => WWW_FIXTURES_DIR,
			'missingAsset' => 'exception',
			'missingManifest' => 'exception',
			'missingRevision' => 'ignore',
		]);

		$template = '{asset 123}';
		$latte->renderToString($template, [
			'basePath' => '/base/path'
		]);
	}

	/**
	 * Test asset macro if second argument has invalid type
	 * @throws \Nette\Utils\AssertionException
	 */
	public function testInvalidTypeFormatArgument() {
		$latte = TestUtils::createLatte();
		$latte->addProvider(AssetMacro::CONFIG_PROVIDER, [
			'manifest' => WWW_FIXTURES_DIR . '/paths-manifest.json',
			'autodetect' => [],
			'wwwDir' => WWW_FIXTURES_DIR,
			'missingAsset' => 'exception',
			'missingManifest' => 'exception',
			'missingRevision' => 'ignore',
		]);

		$template = '{asset "assets/compiled/main.js", 123}';
		$latte->renderToString($template, [
			'basePath' => '/base/path'
		]);
	}

	/**
	 * Test asset macro if third argument has invalid type
	 * @throws \Nette\Utils\AssertionException
	 */
	public function testInvalidTypeNeedArgument() {
		$latte = TestUtils::createLatte();
		$latte->addProvider(AssetMacro::CONFIG_PROVIDER, [
			'manifest' => WWW_FIXTURES_DIR . '/paths-manifest.json',
			'autodetect' => [],
			'wwwDir' => WWW_FIXTURES_DIR,
			'missingAsset' => 'exception',
			'missingManifest' => 'exception',
			'missingRevision' => 'ignore',
		]);

		$template = '{asset "assets/compiled/main.js", "%url%", 123}';
		$latte->renderToString($template, [
			'basePath' => '/base/path'
		]);
	}

	/**
	 * Test if macro ignore missing asset if need is FALSE
	 */
	public function testMissingAssetNeedArgumentFalse() {
		$latte = TestUtils::createLatte();
		$latte->addProvider(AssetMacro::CONFIG_PROVIDER, [
			'manifest' => WWW_FIXTURES_DIR . '/paths-manifest.json',
			'autodetect' => [],
			'wwwDir' => WWW_FIXTURES_DIR,
			'missingAsset' => 'exception',
			'missingManifest' => 'exception',
			'missingRevision' => 'exception',
		]);

		$template = '{asset "assets/compiled/invalid.js", "%url%", false}';
		Assert::same(
			'',
			$latte->renderToString($template, [
				'basePath' => '/base/path'
			])
		);
	}

	/**
	 * Test asset macro with need argument as named parameter
	 */
	public function testNeedNamedParameter() {
		$latte = TestUtils::createLatte();
		$latte->addProvider(AssetMacro::CONFIG_PROVIDER, [
			'manifest' => WWW_FIXTURES_DIR . '/paths-manifest.json',
			'autodetect' => [],
			'wwwDir' => WWW_FIXTURES_DIR,
			'missingAsset' => 'exception',
			'missingManifest' => 'exception',
			'missingRevision' => 'exception',
		]);

		$template = '{asset "invalid.js", need => FALSE}';
		Assert::same(
			'',
			$latte->renderToString($template, [
				'basePath' => '/base/path'
			])
		);
	}

	/**
	 * Test asset macro without arguments
	 * @throws Latte\CompileException
	 */
	public function testWithoutArguments() {
		$latte = TestUtils::createLatte();
		$latte->addProvider(AssetMacro::CONFIG_PROVIDER, [
			'manifest' => WWW_FIXTURES_DIR . '/paths-manifest.json',
			'autodetect' => [],
			'wwwDir' => WWW_FIXTURES_DIR,
			'missingAsset' => 'ignore',
			'missingManifest' => 'ignore',
			'missingRevision' => 'ignore',
		]);

		$template = '{asset}';
		$latte->renderToString($template, [
			'basePath' => '/base/path'
		]);
	}

	/**
	 * Test asset macro with too many arguments
	 * @throws Latte\CompileException
	 */
	public function testTooManyArguments() {
		$latte = TestUtils::createLatte();
		$latte->addProvider(AssetMacro::CONFIG_PROVIDER, [
			'manifest' => WWW_FIXTURES_DIR . '/paths-manifest.json',
			'autodetect' => [],
			'wwwDir' => WWW_FIXTURES_DIR,
			'missingAsset' => 'ignore',
			'missingManifest' => 'ignore',
			'missingRevision' => 'ignore',
		]);

		$template = '{asset "a", "b", "c", "d"}';
		$latte->renderToString($template, [
			'basePath' => '/base/path'
		]);
	}


	/**
	 * Test asset macro format: %url%
	 */
	public function testFormatUrl() {
		$latte = TestUtils::createLatte();
		$latte->addProvider(AssetMacro::CONFIG_PROVIDER, [
			'manifest' => WWW_FIXTURES_DIR . '/paths-manifest.json',
			'autodetect' => [],
			'wwwDir' => WWW_FIXTURES_DIR,
			'missingAsset' => 'exception',
			'missingManifest' => 'exception',
			'missingRevision' => 'exception',
		]);

		$template = '{asset "assets/compiled/main.js", "%url%"}';
		Assert::same(
			'/base/path/assets/compiled/main.fc730c89c4255.js',
			$latte->renderToString($template, [
				'basePath' => '/base/path'
			])
		);
	}

	/**
	 * Test asset macro format: %path%
	 */
	public function testFormatPath() {
		$latte = TestUtils::createLatte();
		$latte->addProvider(AssetMacro::CONFIG_PROVIDER, [
			'manifest' => WWW_FIXTURES_DIR . '/paths-manifest.json',
			'autodetect' => [],
			'wwwDir' => WWW_FIXTURES_DIR,
			'missingAsset' => 'exception',
			'missingManifest' => 'exception',
			'missingRevision' => 'exception',
		]);

		$template = '{asset "assets/compiled/main.js", "%path%"}';
		Assert::same(
			'assets/compiled/main.fc730c89c4255.js',
			$latte->renderToString($template, [
				'basePath' => '/base/path'
			])
		);
	}

	/**
	 * Test asset macro format: %raw%
	 */
	public function testFormatRaw() {
		$latte = TestUtils::createLatte();
		$latte->addProvider(AssetMacro::CONFIG_PROVIDER, [
			'manifest' => WWW_FIXTURES_DIR . '/paths-manifest.json',
			'autodetect' => [],
			'wwwDir' => WWW_FIXTURES_DIR,
			'missingAsset' => 'exception',
			'missingManifest' => 'exception',
			'missingRevision' => 'exception',
		]);

		$template = '{asset "assets/compiled/main.js", "%raw%"}';
		Assert::same(
			'assets/compiled/main.fc730c89c4255.js',
			$latte->renderToString($template, [
				'basePath' => '/base/path'
			])
		);
	}

	/**
	 * Test asset macro format: %basePath%
	 */
	public function testFormatBasePath() {
		$latte = TestUtils::createLatte();
		$latte->addProvider(AssetMacro::CONFIG_PROVIDER, [
			'manifest' => WWW_FIXTURES_DIR . '/paths-manifest.json',
			'autodetect' => [],
			'wwwDir' => WWW_FIXTURES_DIR,
			'missingAsset' => 'exception',
			'missingManifest' => 'exception',
			'missingRevision' => 'exception',
		]);

		$template = '{asset "assets/compiled/main.js", "%basePath%"}';
		Assert::same(
			'/base/path',
			$latte->renderToString($template, [
				'basePath' => '/base/path'
			])
		);
	}


	/**
	 * Test asset macro with format parameter as named parameter
	 */
	public function testFormatNamedParameter() {
		$latte = TestUtils::createLatte();
		$latte->addProvider(AssetMacro::CONFIG_PROVIDER, [
			'manifest' => WWW_FIXTURES_DIR . '/paths-manifest.json',
			'autodetect' => [],
			'wwwDir' => WWW_FIXTURES_DIR,
			'missingAsset' => 'exception',
			'missingManifest' => 'exception',
			'missingRevision' => 'exception',
		]);

		$template = '{asset "assets/compiled/main.js", format => "%raw%"}';
		Assert::same(
			'assets/compiled/main.fc730c89c4255.js',
			$latte->renderToString($template, [
				'basePath' => '/base/path'
			])
		);
	}

	/**
	 * Test asset macro format with invalid variable
	 * @throws \Webrouse\AssetMacro\Exceptions\InvalidVariableException
	 */
	public function testFormatInvalidVariable() {
		$latte = TestUtils::createLatte();
		$latte->addProvider(AssetMacro::CONFIG_PROVIDER, [
			'manifest' => WWW_FIXTURES_DIR . '/paths-manifest.json',
			'autodetect' => [],
			'wwwDir' => WWW_FIXTURES_DIR,
			'missingAsset' => 'exception',
			'missingManifest' => 'exception',
			'missingRevision' => 'exception',
		]);

		$template = '{asset "assets/compiled/main.js", "%abc%"}';
		$latte->renderToString($template, [
			'basePath' => '/base/path'
		]);
	}

	/**
	 * Test asset macro format multiple variables
	 */
	public function testFormatMultipleVars() {
		$latte = TestUtils::createLatte();
		$latte->addProvider(AssetMacro::CONFIG_PROVIDER, [
			'manifest' => WWW_FIXTURES_DIR . '/paths-manifest.json',
			'autodetect' => [],
			'wwwDir' => WWW_FIXTURES_DIR,
			'missingAsset' => 'exception',
			'missingManifest' => 'exception',
			'missingRevision' => 'exception',
		]);

		$template = '{asset "assets/compiled/main.js", "%basePath%/%path%"}';
		Assert::same(
			'/base/path/assets/compiled/main.fc730c89c4255.js',
			$latte->renderToString($template, [
				'basePath' => '/base/path'
			])
		);
	}

	/**
	 * Test if macro throw the exception if revision manifest file not exists
	 * @throws \Webrouse\AssetMacro\Exceptions\ManifestNotFoundException
	 */
	public function testMissingManifestException() {
		$latte = TestUtils::createLatte();
		$latte->addProvider(AssetMacro::CONFIG_PROVIDER, [
			'manifest' => WWW_FIXTURES_DIR . '/version_invalid.json',
			'autodetect' => [],
			'wwwDir' => WWW_FIXTURES_DIR,
			'missingAsset' => 'exception',
			'missingManifest' => 'exception',
			'missingRevision' => 'exception',
		]);

		$template = "{asset 'assets/compiled/main.js'}";
		$latte->renderToString($template, [
			'basePath' => '/base/path'
		]);
	}

	/**
	 * Test if asset macro generate notice if revision manifest file not exists
	 */
	public function testMissingManifestNotice() {
		$latte = TestUtils::createLatte();
		$latte->addProvider(AssetMacro::CONFIG_PROVIDER, [
			'manifest' => WWW_FIXTURES_DIR . '/version_invalid.json',
			'autodetect' => [],
			'wwwDir' => WWW_FIXTURES_DIR,
			'missingAsset' => 'exception',
			'missingManifest' => 'notice',
			'missingRevision' => 'exception',
		]);

		$template = "{asset 'assets/compiled/main.js'}";

		Assert::error(function() use($template, $latte) {
			$latte->renderToString($template, [
				'basePath' => '/base/path'
			]);
		}, E_USER_NOTICE);

		error_reporting(E_ERROR | E_PARSE);
		Assert::same(
			'/base/path/assets/compiled/main.js?v=unknown',
			$latte->renderToString($template, [
				'basePath' => '/base/path'
			])
		);
	}

	/**
	 * Test if asset macro ignore missing revision manifest file
	 */
	public function testMissingManifestIgnore() {
		$latte = TestUtils::createLatte();
		$latte->addProvider(AssetMacro::CONFIG_PROVIDER, [
			'manifest' => WWW_FIXTURES_DIR . '/version_invalid.json',
			'autodetect' => [],
			'wwwDir' => WWW_FIXTURES_DIR,
			'missingAsset' => 'exception',
			'missingManifest' => 'ignore',
			'missingRevision' => 'exception',
		]);

		$template = "{asset 'assets/compiled/main.js'}";

		Assert::same(
			'/base/path/assets/compiled/main.js?v=unknown',
			$latte->renderToString($template, [
				'basePath' => '/base/path'
			])
		);
	}

	/**
	 * Test if version is set to 'unknown', if revision not found in manifest
	 */
	public function testMissingRevisionIgnore() {
		$latte = TestUtils::createLatte();
		$latte->addProvider(AssetMacro::CONFIG_PROVIDER, [
			'manifest' => WWW_FIXTURES_DIR . '/paths-manifest.json',
			'autodetect' => [],
			'wwwDir' => WWW_FIXTURES_DIR,
			'missingAsset' => 'exception',
			'missingManifest' => 'exception',
			'missingRevision' => 'ignore',
		]);

		$template = '{asset "assets/compiled/some.js"}';
		Assert::same(
			'/base/path/assets/compiled/some.js?v=unknown',
			$latte->renderToString($template, [
				'basePath' => '/base/path'
			])
		);
	}

	/**
	 * Test if asset macro generate notice if revision not found in manifest
	 */
	public function testMissingRevisionNotice() {
		$latte = TestUtils::createLatte();
		$latte->addProvider(AssetMacro::CONFIG_PROVIDER, [
			'manifest' => WWW_FIXTURES_DIR . '/paths-manifest.json',
			'autodetect' => [],
			'wwwDir' => WWW_FIXTURES_DIR,
			'missingAsset' => 'exception',
			'missingManifest' => 'exception',
			'missingRevision' => 'notice',
		]);

		$template = '{asset "assets/compiled/some.js"}';

		Assert::error(function() use($template, $latte) {
			$latte->renderToString($template, [
				'basePath' => '/base/path'
			]);
		}, E_USER_NOTICE);

		error_reporting(E_ERROR | E_PARSE);
		Assert::same(
			'/base/path/assets/compiled/some.js?v=unknown',
			$latte->renderToString($template, [
				'basePath' => '/base/path'
			])
		);
	}

	/**
	 * Test if asset macro thrown the exception if revision not found in manifest
	 * @throws \Webrouse\AssetMacro\Exceptions\RevisionNotFound
	 */
	public function testMissingRevisionException() {
		$latte = TestUtils::createLatte();
		$latte->addProvider(AssetMacro::CONFIG_PROVIDER, [
			'manifest' => WWW_FIXTURES_DIR . '/paths-manifest.json',
			'autodetect' => [],
			'wwwDir' => WWW_FIXTURES_DIR,
			'missingAsset' => 'exception',
			'missingManifest' => 'exception',
			'missingRevision' => 'exception',
		]);

		$template = '{asset "assets/compiled/some.js"}';
		$latte->renderToString($template, [
			'basePath' => '/base/path'
		]);
	}

	/**
	 * Test if macro throw the exception if www dir not exists
	 * @throws \Webrouse\AssetMacro\Exceptions\AssetNotFoundException
	 */
	public function testWwwDirNotExists() {
		$latte = TestUtils::createLatte();
		$latte->addProvider(AssetMacro::CONFIG_PROVIDER, [
			'manifest' => WWW_FIXTURES_DIR . '/paths-manifest.json',
			'autodetect' => [],
			'wwwDir' => '/invalid/www/dir',
			'missingAsset' => 'exception',
			'missingManifest' => 'exception',
			'missingRevision' => 'exception',
		]);

		$template = '{asset "assets/compiled/main.js"}';
		$latte->renderToString($template, [
			'basePath' => '/base/path'
		]);
	}

	/**
	 * Test if macro throw the exception if asset not exists
	 * @throws \Webrouse\AssetMacro\Exceptions\AssetNotFoundException
	 */
	public function testMissingAssetException() {
		$latte = TestUtils::createLatte();
		$latte->addProvider(AssetMacro::CONFIG_PROVIDER, [
			'manifest' => WWW_FIXTURES_DIR . '/paths-manifest.json',
			'autodetect' => [],
			'wwwDir' => WWW_FIXTURES_DIR,
			'missingAsset' => 'exception',
			'missingManifest' => 'exception',
			'missingRevision' => 'ignore',
		]);

		$template = '{asset "assets/compiled/invalid.js"}';
		$latte->renderToString($template, [
			'basePath' => '/base/path'
		]);
	}

	/**
	 * Test if macro trigger notice if asset not exists
	 */
	public function testMissingAssetNotice() {
		$latte = TestUtils::createLatte();
		$latte->addProvider(AssetMacro::CONFIG_PROVIDER, [
			'manifest' => WWW_FIXTURES_DIR . '/paths-manifest.json',
			'autodetect' => [],
			'wwwDir' => WWW_FIXTURES_DIR,
			'missingAsset' => 'notice',
			'missingManifest' => 'exception',
			'missingRevision' => 'ignore',
		]);

		$template = '{asset "assets/compiled/invalid.js"}';

		Assert::error(function() use($latte, $template) {
			$latte->renderToString($template, [
				'basePath' => '/base/path'
			]);
		}, E_USER_NOTICE);

		error_reporting(E_ERROR | E_PARSE);
		Assert::same(
			'',
			$latte->renderToString($template, [
				'basePath' => '/base/path'
			])
		);
	}

	/**
	 * Test if macro ignore if asset not exists
	 */
	public function testMissingAssetIgnore() {
		$latte = TestUtils::createLatte();
		$latte->addProvider(AssetMacro::CONFIG_PROVIDER, [
			'manifest' => WWW_FIXTURES_DIR . '/paths-manifest.json',
			'autodetect' => [],
			'wwwDir' => WWW_FIXTURES_DIR,
			'missingAsset' => 'ignore',
			'missingManifest' => 'exception',
			'missingRevision' => 'ignore',
		]);

		$template = '{asset "assets/compiled/invalid.js"}';
		Assert::same(
			'',
			$latte->renderToString($template, [
				'basePath' => '/base/path'
			])
		);
	}
}

(new PathsTest())->run();
