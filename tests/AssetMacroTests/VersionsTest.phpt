<?php
declare(strict_types=1);

namespace Webrouse\AssetMacro;

use Latte;
use Nette\Utils\Strings;
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
		$latte = $this->createLatte();
		Assert::same(
			'/base/path/fixtures/assets/compiled/main.js?v=8c48f58dfc7330c89c42550963c81546',
			$latte->renderToString('{asset "assets/compiled/main.js"}')
		);
	}


	/**
	 * Test asset macro if second argument has invalid type
	 * @throws \Nette\Utils\AssertionException
	 */
	public function testInvalidTypeFormatArgument()
	{
		$latte = $this->createLatte();
		$latte->renderToString('{asset "assets/compiled/main.js", 123}');
	}


	/**
	 * Test asset macro if third argument has invalid type
	 * @throws \Nette\Utils\AssertionException
	 */
	public function testInvalidTypeNeedArgument()
	{
		$latte = $this->createLatte();
		$latte->renderToString('{asset "assets/compiled/main.js", "%url%", 123}');
	}


	/**
	 * Test if macro ignore missing asset if need is FALSE
	 */
	public function testMissingAssetNeedArgumentFalse()
	{
		$latte = $this->createLatte();
		Assert::same(
			'/base/path/fixtures/assets/compiled/invalid.js?v=unknown',
			$latte->renderToString('{asset "assets/compiled/invalid.js", "%url%", false}')
		);
	}


	/**
	 * Test asset macro with need argument as named parameter
	 */
	public function testNeedNamedParameter()
	{
		$latte = $this->createLatte();
		Assert::same(
			'/base/path/fixtures/invalid.js?v=unknown',
			$latte->renderToString('{asset "invalid.js", need => FALSE}')
		);
	}


	/**
	 * Test asset macro without arguments
	 * @throws Latte\CompileException
	 */
	public function testWithoutArguments()
	{
		$latte = $this->createLatte();
		$latte->renderToString('{asset}');
	}


	/**
	 * Test asset macro with too many arguments
	 * @throws Latte\CompileException
	 */
	public function testTooManyArguments()
	{
		$latte = $this->createLatte();
		$latte->renderToString('{asset "a", "b", "c", "d", "e"}');
	}


	/**
	 * Test asset macro modified default format in config
	 */
	public function testDefaultFormat()
	{
		$latte = $this->createLatte(['format' => '/prefix%url%']);
		Assert::same(
			'/prefix/base/path/fixtures/assets/compiled/main.js?v=8c48f58dfc7330c89c42550963c81546',
			$latte->renderToString('{asset "assets/compiled/main.js"}')
		);
	}


	/**
	 * Test asset macro format: %content%
	 */
	public function testFormatContent()
	{
		$latte = $this->createLatte();
		Assert::same(
			'main',
			$latte->renderToString('{asset "assets/compiled/main.js", "%content%"}')
		);
	}


	/**
	 * Test asset macro format: %url%
	 */
	public function testFormatUrl()
	{
		$latte = $this->createLatte();
		Assert::same(
			'/base/path/fixtures/assets/compiled/main.js?v=8c48f58dfc7330c89c42550963c81546',
			$latte->renderToString('{asset "assets/compiled/main.js", "%url%"}')
		);
	}


	/**
	 * Test asset macro format: %path%
	 */
	public function testFormatPath()
	{
		$latte = $this->createLatte();
		Assert::same(
			'assets/compiled/main.js',
			$latte->renderToString('{asset "assets/compiled/main.js", "%path%"}')
		);
	}


	/**
	 * Test asset macro format: %raw%
	 */
	public function testFormatRaw()
	{
		$latte = $this->createLatte();
		Assert::same(
			'8c48f58dfc7330c89c42550963c81546',
			$latte->renderToString('{asset "assets/compiled/main.js", "%raw%"}')
		);
	}


	/**
	 * Test asset macro format: %base% is relative url
	 */
	public function testFormatBaseRelative()
	{
		$latte = $this->createLatte();
		Assert::same(
			'/base/path/fixtures/',
			$latte->renderToString('{asset "assets/compiled/main.js", "%base%"}')
		);
	}


	/**
	 * Test asset macro format: %base% is absolute
	 */
	public function testFormatBaseAbsolute()
	{
		$latte = $this->createLatte();
		Assert::same(
			'http://www.example.com/base/path/fixtures/',
			$latte->renderToString('{asset "//assets/compiled/main.js", "%base%"}')
		);
	}


	/**
	 * Test asset macro format: %baseUrl%
	 */
	public function testFormatBaseUrl()
	{
		$latte = $this->createLatte();
		Assert::same(
			'http://www.example.com/base/path/fixtures/',
			$latte->renderToString('{asset "assets/compiled/main.js", "%baseUrl%"}')
		);
	}


	/**
	 * Test asset macro format: %basePath%
	 */
	public function testFormatBasePath()
	{
		$latte = $this->createLatte();
		Assert::same(
			'/base/path/fixtures/',
			$latte->renderToString('{asset "assets/compiled/main.js", "%basePath%"}')
		);
	}


	/**
	 * Test asset macro with format parameter as named parameter
	 */
	public function testFormatNamedParameter()
	{
		$latte = $this->createLatte();
		Assert::same(
			'8c48f58dfc7330c89c42550963c81546',
			$latte->renderToString('{asset "assets/compiled/main.js", format => "%raw%"}')
		);
	}


	/**
	 * Test asset macro format with invalid variable
	 * @throws \Webrouse\AssetMacro\Exceptions\InvalidVariableException
	 */
	public function testFormatInvalidVariable()
	{
		$latte = $this->createLatte();
		$latte->renderToString('{asset "assets/compiled/main.js", "%abc%"}');
	}


	/**
	 * Test asset macro format multiple variables
	 */
	public function testFormatMultipleVars()
	{
		$latte = $this->createLatte();
		Assert::same(
			'assets/compiled/main.js?v=8c48f58dfc7330c89c42550963c81546',
			$latte->renderToString('{asset "assets/compiled/main.js", "%path%?v=%raw%"}')
		);
	}


	/**
	 * Test if macro throw the exception if revision manifest file not exists
	 * @throws \Webrouse\AssetMacro\Exceptions\ManifestNotFoundException
	 */
	public function testMissingManifestException()
	{
		$latte = $this->createLatte(['missingManifest' => 'exception', 'manifest' => WWW_FIXTURES_DIR . '/invalid.json']);
		$latte->renderToString("{asset 'assets/compiled/main.js'}");
	}


	/**
	 * Test if asset macro generate notice if revision manifest file not exists
	 */
	public function testMissingManifestNotice()
	{
		$latte = $this->createLatte(['missingManifest' => 'notice', 'manifest' => WWW_FIXTURES_DIR . '/invalid.json']);
		$template = "{asset 'assets/compiled/main.js'}";

		Assert::error(function () use ($template, $latte) {
			$latte->renderToString($template);
		}, E_USER_NOTICE);

		error_reporting(E_ERROR | E_PARSE);
		Assert::same(
			'/base/path/fixtures/assets/compiled/main.js?v=unknown',
			$latte->renderToString($template)
		);
	}


	/**
	 * Test if asset macro ignore missing revision manifest file
	 */
	public function testMissingManifestIgnore()
	{
		$latte = $this->createLatte(['missingManifest' => 'ignore', 'manifest' => WWW_FIXTURES_DIR . '/invalid.json']);

		Assert::same(
			'/base/path/fixtures/assets/compiled/main.js?v=unknown',
			$latte->renderToString("{asset 'assets/compiled/main.js'}")
		);
	}


	/**
	 * Test if version is set to 'unknown', if revision not found in manifest
	 */
	public function testMissingRevisionIgnore()
	{
		$latte = $this->createLatte(['missingRevision' => 'ignore']);
		Assert::same(
			'/base/path/fixtures/assets/compiled/some.js?v=unknown',
			$latte->renderToString('{asset "assets/compiled/some.js"}')
		);
	}


	/**
	 * Test if asset macro generate notice if revision not found in manifest
	 */
	public function testMissingRevisionNotice()
	{
		$latte = $this->createLatte(['missingRevision' => 'notice']);
		$template = '{asset "assets/compiled/some.js"}';

		Assert::error(function () use ($template, $latte) {
			$latte->renderToString($template);
		}, E_USER_NOTICE);

		error_reporting(E_ERROR | E_PARSE);
		Assert::same(
			'/base/path/fixtures/assets/compiled/some.js?v=unknown',
			$latte->renderToString($template)
		);
	}


	/**
	 * Test if asset macro thrown the exception if revision not found in manifest
	 * @throws \Webrouse\AssetMacro\Exceptions\RevisionNotFound
	 */
	public function testMissingRevisionException()
	{
		$latte = $this->createLatte(['missingRevision' => 'exception']);
		$latte->renderToString('{asset "assets/compiled/some.js"}');
	}


	/**
	 * Test if macro throw the exception if www dir not exists
	 * @throws \Webrouse\AssetMacro\Exceptions\AssetNotFoundException
	 */
	public function testAssetsDirNotExists()
	{
		$latte = $this->createLatte(['missingAsset' => 'exception', 'assetsPath' => '/abc']);
		$latte->renderToString('{asset "assets/compiled/main.js"}');
	}


	/**
	 * Test if macro throw the exception if asset not exists
	 * @throws \Webrouse\AssetMacro\Exceptions\AssetNotFoundException
	 */
	public function testMissingAssetException()
	{
		$latte = $this->createLatte(['missingAsset' => 'exception']);
		$latte->renderToString('{asset "assets/compiled/invalid.js"}');
	}


	/**
	 * Test if macro trigger notice if asset not exists
	 */
	public function testMissingAssetNotice()
	{
		$latte = $this->createLatte(['missingAsset' => 'notice']);
		$template = '{asset "assets/compiled/invalid.js"}';

		Assert::error(function () use ($latte, $template) {
			$latte->renderToString($template);
		}, E_USER_NOTICE);

		error_reporting(E_ERROR | E_PARSE);
		Assert::same(
			'/base/path/fixtures/assets/compiled/invalid.js?v=unknown',
			$latte->renderToString($template)
		);
	}


	/**
	 * Test if macro ignore if asset not exists
	 */
	public function testMissingAssetIgnore()
	{
		$latte = $this->createLatte(['missingAsset' => 'ignore']);
		Assert::same(
			'/base/path/fixtures/assets/compiled/invalid.js?v=unknown',
			$latte->renderToString('{asset "assets/compiled/invalid.js"}')
		);
	}


	/**
	 * Test if macro ignore start slashes in manifest
	 */
	public function testStripOptionalSlashes()
	{
		$latte = $this->createLatte();
		Assert::same(
			'/base/path/fixtures/assets/compiled/other.js?v=8h9hfj5vvh4jffokvzj6h1fjfnfd9c',
			$latte->renderToString('{asset "/assets/compiled/other.js"}')
		);
	}


	/**
	 * Test if macro generates absolute path, if asset path starts with //
	 */
	public function testAbsolutePathByPathPrefix()
	{
		$latte = $this->createLatte();
		Assert::same(
			'http://www.example.com/base/path/fixtures/assets/compiled/other.js?v=8h9hfj5vvh4jffokvzj6h1fjfnfd9c',
			$latte->renderToString('{asset "//assets/compiled/other.js"}')
		);
	}


	/**
	 * Test if macro generates absolute path, if absolute=true
	 */
	public function testAbsolutePathByParameter()
	{
		$latte = $this->createLatte();
		Assert::same(
			'http://www.example.com/base/path/fixtures/assets/compiled/other.js?v=8h9hfj5vvh4jffokvzj6h1fjfnfd9c',
			$latte->renderToString('{asset "/assets/compiled/other.js", absolute => true}')
		);
	}


	/**
	 * Test if macro generates absolute path, if argument true
	 */
	public function testAbsolutePathByArgument()
	{
		$latte = $this->createLatte();
		Assert::same(
			'http://www.example.com/base/path/fixtures/assets/compiled/other.js?v=8h9hfj5vvh4jffokvzj6h1fjfnfd9c',
			$latte->renderToString('{asset "/assets/compiled/other.js", "%url%", true, true}')
		);
	}


	public function testManifestGetAll(): void
	{
		$service = $this->createService();
		$assets = $service->getManifest()->getAll();
		Assert::same([
			'assets/compiled/main.css' => 'http://www.example.com/base/path/fixtures/assets/compiled/main.css?v=32ecae4b82916016edc74db0f5a11ceb',
			'assets/compiled/main.js' => 'http://www.example.com/base/path/fixtures/assets/compiled/main.js?v=8c48f58dfc7330c89c42550963c81546',
			'assets/compiled/other.js' => 'http://www.example.com/base/path/fixtures/assets/compiled/other.js?v=8h9hfj5vvh4jffokvzj6h1fjfnfd9c',
			'assets/compiled/escape.js' => 'http://www.example.com/base/path/fixtures/assets/compiled/escape.js?v="escape"',
		], array_map(function (?Asset $asset) use ($service) {
			return $asset ? $service->getFormatter()->format($asset, '%url%', true) : '__null__';
		}, $assets));
	}


	public function testManifestGetAllFilterGlob(): void
	{
		$service = $this->createService();
		$assets1 = $service->getManifest()->getAll('/.*\.css$/');
		Assert::same([
			'assets/compiled/main.css' => 'http://www.example.com/base/path/fixtures/assets/compiled/main.css?v=32ecae4b82916016edc74db0f5a11ceb',
		], array_map(function (?Asset $asset) use ($service) {
			return $asset ? $service->getFormatter()->format($asset, '%url%', true) : '__null__';
		}, $assets1));

		$assets2 = $service->getManifest()->getAll('/.*\.js/');
		Assert::same([
			'assets/compiled/main.js' => 'http://www.example.com/base/path/fixtures/assets/compiled/main.js?v=8c48f58dfc7330c89c42550963c81546',
			'assets/compiled/other.js' => 'http://www.example.com/base/path/fixtures/assets/compiled/other.js?v=8h9hfj5vvh4jffokvzj6h1fjfnfd9c',
			'assets/compiled/escape.js' => 'http://www.example.com/base/path/fixtures/assets/compiled/escape.js?v="escape"',
		], array_map(function (?Asset $asset) use ($service) {
			return $asset ? $service->getFormatter()->format($asset, '%url%', true) : '__null__';
		}, $assets2));
	}


	public function testManifestGetAllFilterCallback(): void
	{
		$service = $this->createService();
		$assets = $service->getManifest()->getAll(function (string $asset) {return Strings::contains($asset, 'other.js');});
		Assert::same([
			'assets/compiled/other.js' => 'http://www.example.com/base/path/fixtures/assets/compiled/other.js?v=8h9hfj5vvh4jffokvzj6h1fjfnfd9c',
		], array_map(function (?Asset $asset) use ($service) {
			return $asset ? $service->getFormatter()->format($asset, '%url%', true) : '__null__';
		}, $assets));
	}


	protected function createService(array $config = []): ManifestService
	{
		return parent::createService(array_merge([
			'missingAsset' => 'ignore',
			'missingManifest' => 'ignore',
			'missingRevision' => 'ignore',
			'manifest' => WWW_FIXTURES_DIR . '/versions-manifest.json',
			'autodetect' => [],
		], $config));
	}
}

(new VersionsTest())->run();
