<?php
declare(strict_types=1);

namespace Webrouse\AssetMacro;

use Latte;
use Tester\Assert;

include '../bootstrap.php';

/**
 * Test asset macro with revision manifest file with format ASSET => VERSION
 * @testCase
 */
class VersionsTest extends TestCase
{

	/**
	 * Test asset macro render
	 */
	public function testRender()
	{
		$latte = TestUtils::createLatte();
		$latte->addProvider(AssetMacro::CONFIG_PROVIDER, [
			'cache' => false,
			'manifest' => WWW_FIXTURES_DIR . '/versions-manifest.json',
			'autodetect' => [],
			'wwwDir' => WWW_FIXTURES_DIR,
			'missingAsset' => 'notice',
			'missingManifest' => 'notice',
			'missingRevision' => 'notice',
			'format' => '%url%',
		]);

		$template = '{asset "assets/compiled/main.js"}';

		Assert::same(
			'/base/path/assets/compiled/main.js?v=8c48f58dfc7330c89c42550963c81546',
			$latte->renderToString($template, self::LATTE_VARS)
		);
	}


	/**
	 * Test asset macro if second argument has invalid type
	 * @throws \Nette\Utils\AssertionException
	 */
	public function testInvalidTypeFormatArgument()
	{
		$latte = TestUtils::createLatte();
		$latte->addProvider(AssetMacro::CONFIG_PROVIDER, [
			'cache' => false,
			'manifest' => WWW_FIXTURES_DIR . '/versions-manifest.json',
			'autodetect' => [],
			'wwwDir' => WWW_FIXTURES_DIR,
			'missingAsset' => 'exception',
			'missingManifest' => 'exception',
			'missingRevision' => 'notice',
			'format' => '%url%',
		]);

		$template = '{asset "assets/compiled/main.js", 123}';
		$latte->renderToString($template, self::LATTE_VARS);
	}


	/**
	 * Test asset macro if third argument has invalid type
	 * @throws \Nette\Utils\AssertionException
	 */
	public function testInvalidTypeNeedArgument()
	{
		$latte = TestUtils::createLatte();
		$latte->addProvider(AssetMacro::CONFIG_PROVIDER, [
			'cache' => false,
			'manifest' => WWW_FIXTURES_DIR . '/versions-manifest.json',
			'autodetect' => [],
			'wwwDir' => WWW_FIXTURES_DIR,
			'missingAsset' => 'exception',
			'missingManifest' => 'exception',
			'missingRevision' => 'notice',
			'format' => '%url%',
		]);

		$template = '{asset "assets/compiled/main.js", "%url%", 123}';
		$latte->renderToString($template, self::LATTE_VARS);
	}


	/**
	 * Test if macro ignore missing asset if need is FALSE
	 */
	public function testMissingAssetNeedArgumentFalse()
	{
		$latte = TestUtils::createLatte();
		$latte->addProvider(AssetMacro::CONFIG_PROVIDER, [
			'cache' => false,
			'manifest' => WWW_FIXTURES_DIR . '/versions-manifest.json',
			'autodetect' => [],
			'wwwDir' => WWW_FIXTURES_DIR,
			'missingAsset' => 'exception',
			'missingManifest' => 'exception',
			'missingRevision' => 'exception',
			'format' => '%url%',
		]);

		$template = '{asset "assets/compiled/invalid.js", "%url%", false}';
		Assert::same(
			'',
			$latte->renderToString($template, self::LATTE_VARS)
		);
	}


	/**
	 * Test asset macro with need argument as named parameter
	 */
	public function testNeedNamedParameter()
	{
		$latte = TestUtils::createLatte();
		$latte->addProvider(AssetMacro::CONFIG_PROVIDER, [
			'cache' => false,
			'manifest' => WWW_FIXTURES_DIR . '/versions-manifest.json',
			'autodetect' => [],
			'wwwDir' => WWW_FIXTURES_DIR,
			'missingAsset' => 'exception',
			'missingManifest' => 'exception',
			'missingRevision' => 'exception',
			'format' => '%url%',
		]);

		$template = '{asset "invalid.js", need => FALSE}';
		Assert::same(
			'',
			$latte->renderToString($template, self::LATTE_VARS)
		);
	}


	/**
	 * Test asset macro without arguments
	 * @throws Latte\CompileException
	 */
	public function testWithoutArguments()
	{
		$latte = TestUtils::createLatte();
		$latte->addProvider(AssetMacro::CONFIG_PROVIDER, [
			'cache' => false,
			'manifest' => WWW_FIXTURES_DIR . '/versions-manifest.json',
			'autodetect' => [],
			'wwwDir' => WWW_FIXTURES_DIR,
			'missingAsset' => 'exception',
			'missingManifest' => 'exception',
			'missingRevision' => 'exception',
			'format' => '%url%',
		]);

		$template = '{asset}';
		$latte->renderToString($template, self::LATTE_VARS);
	}


	/**
	 * Test asset macro with too many arguments
	 * @throws Latte\CompileException
	 */
	public function testTooManyArguments()
	{
		$latte = TestUtils::createLatte();
		$latte->addProvider(AssetMacro::CONFIG_PROVIDER, [
			'cache' => false,
			'manifest' => WWW_FIXTURES_DIR . '/versions-manifest.json',
			'autodetect' => [],
			'wwwDir' => WWW_FIXTURES_DIR,
			'missingAsset' => 'exception',
			'missingManifest' => 'exception',
			'missingRevision' => 'exception',
			'format' => '%url%',
		]);

		$template = '{asset "a", "b", "c", "d", "e"}';
		$latte->renderToString($template, self::LATTE_VARS);
	}


	/**
	 * Test asset macro modified default format in config
	 */
	public function testDefaultFormat()
	{
		$latte = TestUtils::createLatte();
		$latte->addProvider(AssetMacro::CONFIG_PROVIDER, [
			'cache' => false,
			'manifest' => WWW_FIXTURES_DIR . '/versions-manifest.json',
			'autodetect' => [],
			'wwwDir' => WWW_FIXTURES_DIR,
			'missingAsset' => 'exception',
			'missingManifest' => 'exception',
			'missingRevision' => 'exception',
			'format' => '/prefix%url%',
		]);

		$template = '{asset "assets/compiled/main.js"}';
		Assert::same(
			'/prefix/base/path/assets/compiled/main.js?v=8c48f58dfc7330c89c42550963c81546',
			$latte->renderToString($template, self::LATTE_VARS)
		);
	}


	/**
	 * Test asset macro format: %content%
	 */
	public function testFormatContent()
	{
		$latte = TestUtils::createLatte();
		$latte->addProvider(AssetMacro::CONFIG_PROVIDER, [
			'cache' => false,
			'manifest' => WWW_FIXTURES_DIR . '/versions-manifest.json',
			'autodetect' => [],
			'wwwDir' => WWW_FIXTURES_DIR,
			'missingAsset' => 'exception',
			'missingManifest' => 'exception',
			'missingRevision' => 'exception',
			'format' => '%url%',
		]);

		$template = '{asset "assets/compiled/main.js", "%content%"}';
		Assert::same(
			'main',
			$latte->renderToString($template, self::LATTE_VARS)
		);
	}


	/**
	 * Test asset macro format: %url%
	 */
	public function testFormatUrl()
	{
		$latte = TestUtils::createLatte();
		$latte->addProvider(AssetMacro::CONFIG_PROVIDER, [
			'cache' => false,
			'manifest' => WWW_FIXTURES_DIR . '/versions-manifest.json',
			'autodetect' => [],
			'wwwDir' => WWW_FIXTURES_DIR,
			'missingAsset' => 'exception',
			'missingManifest' => 'exception',
			'missingRevision' => 'exception',
			'format' => '%url%',
		]);

		$template = '{asset "assets/compiled/main.js", "%url%"}';
		Assert::same(
			'/base/path/assets/compiled/main.js?v=8c48f58dfc7330c89c42550963c81546',
			$latte->renderToString($template, self::LATTE_VARS)
		);
	}


	/**
	 * Test asset macro format: %path%
	 */
	public function testFormatPath()
	{
		$latte = TestUtils::createLatte();
		$latte->addProvider(AssetMacro::CONFIG_PROVIDER, [
			'cache' => false,
			'manifest' => WWW_FIXTURES_DIR . '/versions-manifest.json',
			'autodetect' => [],
			'wwwDir' => WWW_FIXTURES_DIR,
			'missingAsset' => 'exception',
			'missingManifest' => 'exception',
			'missingRevision' => 'exception',
			'format' => '%url%',
		]);

		$template = '{asset "assets/compiled/main.js", "%path%"}';
		Assert::same(
			'assets/compiled/main.js',
			$latte->renderToString($template, self::LATTE_VARS)
		);
	}


	/**
	 * Test asset macro format: %raw%
	 */
	public function testFormatRaw()
	{
		$latte = TestUtils::createLatte();
		$latte->addProvider(AssetMacro::CONFIG_PROVIDER, [
			'cache' => false,
			'manifest' => WWW_FIXTURES_DIR . '/versions-manifest.json',
			'autodetect' => [],
			'wwwDir' => WWW_FIXTURES_DIR,
			'missingAsset' => 'exception',
			'missingManifest' => 'exception',
			'missingRevision' => 'exception',
			'format' => '%url%',
		]);

		$template = '{asset "assets/compiled/main.js", "%raw%"}';
		Assert::same(
			'8c48f58dfc7330c89c42550963c81546',
			$latte->renderToString($template, self::LATTE_VARS)
		);
	}


	/**
	 * Test asset macro format: %base% is relative url
	 */
	public function testFormatBaseRelative()
	{
		$latte = TestUtils::createLatte();
		$latte->addProvider(AssetMacro::CONFIG_PROVIDER, [
			'cache' => false,
			'manifest' => WWW_FIXTURES_DIR . '/versions-manifest.json',
			'autodetect' => [],
			'wwwDir' => WWW_FIXTURES_DIR,
			'missingAsset' => 'exception',
			'missingManifest' => 'exception',
			'missingRevision' => 'exception',
			'format' => '%url%',
		]);

		$template = '{asset "assets/compiled/main.js", "%base%"}';
		Assert::same(
			'/base/path',
			$latte->renderToString($template, self::LATTE_VARS)
		);
	}


	/**
	 * Test asset macro format: %base% is absolute
	 */
	public function testFormatBaseAbsolute()
	{
		$latte = TestUtils::createLatte();
		$latte->addProvider(AssetMacro::CONFIG_PROVIDER, [
			'cache' => false,
			'manifest' => WWW_FIXTURES_DIR . '/versions-manifest.json',
			'autodetect' => [],
			'wwwDir' => WWW_FIXTURES_DIR,
			'missingAsset' => 'exception',
			'missingManifest' => 'exception',
			'missingRevision' => 'exception',
			'format' => '%url%',
		]);

		$template = '{asset "//assets/compiled/main.js", "%base%"}';
		Assert::same(
			'http://www.example.com/base/path',
			$latte->renderToString($template, self::LATTE_VARS)
		);
	}


	/**
	 * Test asset macro format: %baseUrl%
	 */
	public function testFormatBaseUrl()
	{
		$latte = TestUtils::createLatte();
		$latte->addProvider(AssetMacro::CONFIG_PROVIDER, [
			'cache' => false,
			'manifest' => WWW_FIXTURES_DIR . '/versions-manifest.json',
			'autodetect' => [],
			'wwwDir' => WWW_FIXTURES_DIR,
			'missingAsset' => 'exception',
			'missingManifest' => 'exception',
			'missingRevision' => 'exception',
			'format' => '%url%',
		]);

		$template = '{asset "assets/compiled/main.js", "%baseUrl%"}';
		Assert::same(
			'http://www.example.com/base/path',
			$latte->renderToString($template, self::LATTE_VARS)
		);
	}


	/**
	 * Test asset macro format: %basePath%
	 */
	public function testFormatBasePath()
	{
		$latte = TestUtils::createLatte();
		$latte->addProvider(AssetMacro::CONFIG_PROVIDER, [
			'cache' => false,
			'manifest' => WWW_FIXTURES_DIR . '/versions-manifest.json',
			'autodetect' => [],
			'wwwDir' => WWW_FIXTURES_DIR,
			'missingAsset' => 'exception',
			'missingManifest' => 'exception',
			'missingRevision' => 'exception',
			'format' => '%url%',
		]);

		$template = '{asset "assets/compiled/main.js", "%basePath%"}';
		Assert::same(
			'/base/path',
			$latte->renderToString($template, self::LATTE_VARS)
		);
	}


	/**
	 * Test asset macro with format parameter as named parameter
	 */
	public function testFormatNamedParameter()
	{
		$latte = TestUtils::createLatte();
		$latte->addProvider(AssetMacro::CONFIG_PROVIDER, [
			'cache' => false,
			'manifest' => WWW_FIXTURES_DIR . '/versions-manifest.json',
			'autodetect' => [],
			'wwwDir' => WWW_FIXTURES_DIR,
			'missingAsset' => 'exception',
			'missingManifest' => 'exception',
			'missingRevision' => 'exception',
			'format' => '%url%',
		]);

		$template = '{asset "assets/compiled/main.js", format => "%raw%"}';
		Assert::same(
			'8c48f58dfc7330c89c42550963c81546',
			$latte->renderToString($template, self::LATTE_VARS)
		);
	}


	/**
	 * Test asset macro format with invalid variable
	 * @throws \Webrouse\AssetMacro\Exceptions\InvalidVariableException
	 */
	public function testFormatInvalidVariable()
	{
		$latte = TestUtils::createLatte();
		$latte->addProvider(AssetMacro::CONFIG_PROVIDER, [
			'cache' => false,
			'manifest' => WWW_FIXTURES_DIR . '/versions-manifest.json',
			'autodetect' => [],
			'wwwDir' => WWW_FIXTURES_DIR,
			'missingAsset' => 'ignore',
			'missingManifest' => 'ignore',
			'missingRevision' => 'notice',
			'format' => '%url%',
		]);

		$template = '{asset "assets/compiled/main.js", "%abc%"}';
		$latte->renderToString($template, self::LATTE_VARS);
	}


	/**
	 * Test asset macro format multiple variables
	 */
	public function testFormatMultipleVars()
	{
		$latte = TestUtils::createLatte();
		$latte->addProvider(AssetMacro::CONFIG_PROVIDER, [
			'cache' => false,
			'manifest' => WWW_FIXTURES_DIR . '/versions-manifest.json',
			'autodetect' => [],
			'wwwDir' => WWW_FIXTURES_DIR,
			'missingAsset' => 'exception',
			'missingManifest' => 'exception',
			'missingRevision' => 'exception',
			'format' => '%url%',
		]);

		$template = '{asset "assets/compiled/main.js", "%path%?v=%raw%"}';
		Assert::same(
			'assets/compiled/main.js?v=8c48f58dfc7330c89c42550963c81546',
			$latte->renderToString($template, self::LATTE_VARS)
		);
	}


	/**
	 * Test if macro throw the exception if revision manifest file not exists
	 * @throws \Webrouse\AssetMacro\Exceptions\ManifestNotFoundException
	 */
	public function testMissingManifestException()
	{
		$latte = TestUtils::createLatte();
		$latte->addProvider(AssetMacro::CONFIG_PROVIDER, [
			'cache' => false,
			'manifest' => WWW_FIXTURES_DIR . '/version_invalid.json',
			'autodetect' => [],
			'wwwDir' => WWW_FIXTURES_DIR,
			'missingAsset' => 'exception',
			'missingManifest' => 'exception',
			'missingRevision' => 'exception',
			'format' => '%url%',
		]);

		$template = "{asset 'assets/compiled/main.js'}";
		$latte->renderToString($template, self::LATTE_VARS);
	}


	/**
	 * Test if asset macro generate notice if revision manifest file not exists
	 */
	public function testMissingManifestNotice()
	{
		$latte = TestUtils::createLatte();
		$latte->addProvider(AssetMacro::CONFIG_PROVIDER, [
			'cache' => false,
			'manifest' => WWW_FIXTURES_DIR . '/version_invalid.json',
			'autodetect' => [],
			'wwwDir' => WWW_FIXTURES_DIR,
			'missingAsset' => 'exception',
			'missingManifest' => 'notice',
			'missingRevision' => 'exception',
			'format' => '%url%',
		]);

		$template = "{asset 'assets/compiled/main.js'}";

		Assert::error(function () use ($template, $latte) {
			$latte->renderToString($template, self::LATTE_VARS);
		}, E_USER_NOTICE);

		error_reporting(E_ERROR | E_PARSE);
		Assert::same(
			'/base/path/assets/compiled/main.js?v=unknown',
			$latte->renderToString($template, self::LATTE_VARS)
		);
	}


	/**
	 * Test if asset macro ignore missing revision manifest file
	 */
	public function testMissingManifestIgnore()
	{
		$latte = TestUtils::createLatte();
		$latte->addProvider(AssetMacro::CONFIG_PROVIDER, [
			'cache' => false,
			'manifest' => WWW_FIXTURES_DIR . '/version_invalid.json',
			'autodetect' => [],
			'wwwDir' => WWW_FIXTURES_DIR,
			'missingAsset' => 'exception',
			'missingManifest' => 'ignore',
			'missingRevision' => 'exception',
			'format' => '%url%',
		]);

		$template = "{asset 'assets/compiled/main.js'}";

		Assert::same(
			'/base/path/assets/compiled/main.js?v=unknown',
			$latte->renderToString($template, self::LATTE_VARS)
		);
	}


	/**
	 * Test if version is set to 'unknown', if revision not found in manifest
	 */
	public function testMissingRevisionIgnore()
	{
		$latte = TestUtils::createLatte();
		$latte->addProvider(AssetMacro::CONFIG_PROVIDER, [
			'cache' => false,
			'manifest' => WWW_FIXTURES_DIR . '/versions-manifest.json',
			'autodetect' => [],
			'wwwDir' => WWW_FIXTURES_DIR,
			'missingAsset' => 'exception',
			'missingManifest' => 'exception',
			'missingRevision' => 'ignore',
			'format' => '%url%',
		]);

		$template = '{asset "assets/compiled/some.js"}';
		Assert::same(
			'/base/path/assets/compiled/some.js?v=unknown',
			$latte->renderToString($template, self::LATTE_VARS)
		);
	}


	/**
	 * Test if asset macro generate notice if revision not found in manifest
	 */
	public function testMissingRevisionNotice()
	{
		$latte = TestUtils::createLatte();
		$latte->addProvider(AssetMacro::CONFIG_PROVIDER, [
			'cache' => false,
			'manifest' => WWW_FIXTURES_DIR . '/versions-manifest.json',
			'autodetect' => [],
			'wwwDir' => WWW_FIXTURES_DIR,
			'missingAsset' => 'exception',
			'missingManifest' => 'exception',
			'missingRevision' => 'notice',
			'format' => '%url%',
		]);

		$template = '{asset "assets/compiled/some.js"}';

		Assert::error(function () use ($template, $latte) {
			$latte->renderToString($template, self::LATTE_VARS);
		}, E_USER_NOTICE);

		error_reporting(E_ERROR | E_PARSE);
		Assert::same(
			'/base/path/assets/compiled/some.js?v=unknown',
			$latte->renderToString($template, self::LATTE_VARS)
		);
	}


	/**
	 * Test if asset macro thrown the exception if revision not found in manifest
	 * @throws \Webrouse\AssetMacro\Exceptions\RevisionNotFound
	 */
	public function testMissingRevisionException()
	{
		$latte = TestUtils::createLatte();
		$latte->addProvider(AssetMacro::CONFIG_PROVIDER, [
			'cache' => false,
			'manifest' => WWW_FIXTURES_DIR . '/versions-manifest.json',
			'autodetect' => [],
			'wwwDir' => WWW_FIXTURES_DIR,
			'missingAsset' => 'exception',
			'missingManifest' => 'exception',
			'missingRevision' => 'exception',
			'format' => '%url%',
		]);

		$template = '{asset "assets/compiled/some.js"}';
		$latte->renderToString($template, self::LATTE_VARS);
	}


	/**
	 * Test if macro throw the exception if www dir not exists
	 * @throws \Webrouse\AssetMacro\Exceptions\AssetNotFoundException
	 */
	public function testWwwDirNotExists()
	{
		$latte = TestUtils::createLatte();
		$latte->addProvider(AssetMacro::CONFIG_PROVIDER, [
			'cache' => false,
			'manifest' => WWW_FIXTURES_DIR . '/versions-manifest.json',
			'autodetect' => [],
			'wwwDir' => '/invalid/www/dir',
			'missingAsset' => 'exception',
			'missingManifest' => 'exception',
			'missingRevision' => 'exception',
			'format' => '%url%',
		]);

		$template = '{asset "assets/compiled/main.js"}';
		$latte->renderToString($template, self::LATTE_VARS);
	}


	/**
	 * Test if macro throw the exception if asset not exists
	 * @throws \Webrouse\AssetMacro\Exceptions\AssetNotFoundException
	 */
	public function testMissingAssetException()
	{
		$latte = TestUtils::createLatte();
		$latte->addProvider(AssetMacro::CONFIG_PROVIDER, [
			'cache' => false,
			'manifest' => WWW_FIXTURES_DIR . '/versions-manifest.json',
			'autodetect' => [],
			'wwwDir' => WWW_FIXTURES_DIR,
			'missingAsset' => 'exception',
			'missingManifest' => 'exception',
			'missingRevision' => 'ignore',
			'format' => '%url%',
		]);

		$template = '{asset "assets/compiled/invalid.js"}';
		$latte->renderToString($template, self::LATTE_VARS);
	}


	/**
	 * Test if macro trigger notice if asset not exists
	 */
	public function testMissingAssetNotice()
	{
		$latte = TestUtils::createLatte();
		$latte->addProvider(AssetMacro::CONFIG_PROVIDER, [
			'cache' => false,
			'manifest' => WWW_FIXTURES_DIR . '/versions-manifest.json',
			'autodetect' => [],
			'wwwDir' => WWW_FIXTURES_DIR,
			'missingAsset' => 'notice',
			'missingManifest' => 'exception',
			'missingRevision' => 'ignore',
			'format' => '%url%',
		]);

		$template = '{asset "assets/compiled/invalid.js"}';

		Assert::error(function () use ($latte, $template) {
			$latte->renderToString($template, self::LATTE_VARS);
		}, E_USER_NOTICE);

		error_reporting(E_ERROR | E_PARSE);
		Assert::same(
			'',
			$latte->renderToString($template, self::LATTE_VARS)
		);
	}


	/**
	 * Test if macro ignore if asset not exists
	 */
	public function testMissingAssetIgnore()
	{
		$latte = TestUtils::createLatte();
		$latte->addProvider(AssetMacro::CONFIG_PROVIDER, [
			'cache' => false,
			'manifest' => WWW_FIXTURES_DIR . '/versions-manifest.json',
			'autodetect' => [],
			'wwwDir' => WWW_FIXTURES_DIR,
			'missingAsset' => 'ignore',
			'missingManifest' => 'exception',
			'missingRevision' => 'ignore',
			'format' => '%url%',
		]);

		$template = '{asset "assets/compiled/invalid.js"}';
		Assert::same(
			'',
			$latte->renderToString($template, self::LATTE_VARS)
		);
	}


	/**
	 * Test if macro ignore start slashes in manifest
	 */
	public function testStripOptionalSlashes()
	{
		$latte = TestUtils::createLatte();
		$latte->addProvider(AssetMacro::CONFIG_PROVIDER, [
			'cache' => false,
			'manifest' => WWW_FIXTURES_DIR . '/versions-manifest.json',
			'autodetect' => [],
			'wwwDir' => WWW_FIXTURES_DIR,
			'missingAsset' => 'exception',
			'missingManifest' => 'exception',
			'missingRevision' => 'exception',
			'format' => '%url%',
		]);

		$template = '{asset "/assets/compiled/other.js"}';
		Assert::same(
			'/base/path/assets/compiled/other.js?v=8h9hfj5vvh4jffokvzj6h1fjfnfd9c',
			$latte->renderToString($template, self::LATTE_VARS)
		);
	}


	/**
	 * Test if macro generates absolute path, if asset path starts with //
	 */
	public function testAbsolutePathByPathPrefix()
	{
		$latte = TestUtils::createLatte();
		$latte->addProvider(AssetMacro::CONFIG_PROVIDER, [
			'cache' => false,
			'manifest' => WWW_FIXTURES_DIR . '/versions-manifest.json',
			'autodetect' => [],
			'wwwDir' => WWW_FIXTURES_DIR,
			'missingAsset' => 'exception',
			'missingManifest' => 'exception',
			'missingRevision' => 'exception',
			'format' => '%url%',
		]);

		$template = '{asset "//assets/compiled/other.js"}';
		Assert::same(
			'http://www.example.com/base/path/assets/compiled/other.js?v=8h9hfj5vvh4jffokvzj6h1fjfnfd9c',
			$latte->renderToString($template, self::LATTE_VARS)
		);
	}


	/**
	 * Test if macro generates absolute path, if absolute=true
	 */
	public function testAbsolutePathByParameter()
	{
		$latte = TestUtils::createLatte();
		$latte->addProvider(AssetMacro::CONFIG_PROVIDER, [
			'cache' => false,
			'manifest' => WWW_FIXTURES_DIR . '/versions-manifest.json',
			'autodetect' => [],
			'wwwDir' => WWW_FIXTURES_DIR,
			'missingAsset' => 'exception',
			'missingManifest' => 'exception',
			'missingRevision' => 'exception',
			'format' => '%url%',
		]);

		$template = '{asset "/assets/compiled/other.js", absolute => true}';
		Assert::same(
			'http://www.example.com/base/path/assets/compiled/other.js?v=8h9hfj5vvh4jffokvzj6h1fjfnfd9c',
			$latte->renderToString($template, self::LATTE_VARS)
		);
	}


	/**
	 * Test if macro generates absolute path, if argument true
	 */
	public function testAbsolutePathByArgument()
	{
		$latte = TestUtils::createLatte();
		$latte->addProvider(AssetMacro::CONFIG_PROVIDER, [
			'cache' => false,
			'manifest' => WWW_FIXTURES_DIR . '/versions-manifest.json',
			'autodetect' => [],
			'wwwDir' => WWW_FIXTURES_DIR,
			'missingAsset' => 'exception',
			'missingManifest' => 'exception',
			'missingRevision' => 'exception',
			'format' => '%url%',
		]);

		$template = '{asset "/assets/compiled/other.js", "%url%", true, true}';
		Assert::same(
			'http://www.example.com/base/path/assets/compiled/other.js?v=8h9hfj5vvh4jffokvzj6h1fjfnfd9c',
			$latte->renderToString($template, self::LATTE_VARS)
		);
	}
}

(new VersionsTest())->run();
