<?php
declare(strict_types=1);

namespace Webrouse\AssetMacro;

use Mockery;
use Nette;
use Tester\Assert;
use Tester\TestCase;

include '../bootstrap.php';

class CacheTest extends TestCase
{
	/**
	 * Test cache storage missing
	 */
	public function testCacheStorageMissing()
	{
		$latte = TestUtils::createLatte();
		$latte->addProvider(AssetMacro::CONFIG_PROVIDER, [
			'cache' => true,
			'manifest' => WWW_FIXTURES_DIR . '/paths-manifest.json',
			'autodetect' => [],
			'wwwDir' => WWW_FIXTURES_DIR,
			'missingAsset' => 'notice',
			'missingManifest' => 'notice',
			'missingRevision' => 'notice',
		]);

		$template = '{asset "assets/compiled/main.js"}';
		Assert::same(
			'/base/path/assets/compiled/main.fc730c89c4255.js',
			$latte->renderToString($template, [
				'basePath' => '/base/path',
			])
		);
	}


	/**
	 * Test disabled cache
	 */
	public function testCacheDisabled()
	{
		$latte = TestUtils::createLatte();
		$latte->addProvider(AssetMacro::CONFIG_PROVIDER, [
			'cache' => false,
			'manifest' => WWW_FIXTURES_DIR . '/paths-manifest.json',
			'autodetect' => [],
			'wwwDir' => WWW_FIXTURES_DIR,
			'missingAsset' => 'notice',
			'missingManifest' => 'notice',
			'missingRevision' => 'notice',
		]);

		// No method from cache storage could be called
		$cacheStorage = Mockery::mock(Nette\Caching\IStorage::class);
		$latte->addProvider('cacheStorage', $cacheStorage);

		$template = '{asset "assets/compiled/main.js"}';
		Assert::same(
			'/base/path/assets/compiled/main.fc730c89c4255.js',
			$latte->renderToString($template, [
				'basePath' => '/base/path',
			])
		);
	}


	/**
	 * Test cache enabled and key is missing
	 */
	public function testCacheEnabledKeyMissing()
	{
		$latte = TestUtils::createLatte();
		$latte->addProvider(AssetMacro::CONFIG_PROVIDER, [
			'cache' => true,
			'manifest' => WWW_FIXTURES_DIR . '/paths-manifest.json',
			'autodetect' => [],
			'wwwDir' => WWW_FIXTURES_DIR,
			'missingAsset' => 'notice',
			'missingManifest' => 'notice',
			'missingRevision' => 'notice',
		]);

		$cacheStorage = Mockery::mock(Nette\Caching\IStorage::class);
		$cacheStorage
			->shouldReceive('read')
			->once()
			->andReturn(null);
		$cacheStorage
			->shouldReceive('write')
			->with(Mockery::any(), '/base/path/assets/compiled/main.fc730c89c4255.js', Mockery::any())
			->once()
			->andReturn(null);

		$latte->addProvider('cacheStorage', $cacheStorage);

		$template = '{asset "assets/compiled/main.js"}';
		Assert::same(
			'/base/path/assets/compiled/main.fc730c89c4255.js',
			$latte->renderToString($template, [
				'basePath' => '/base/path',
			])
		);
	}


	/**
	 * Test cache enabled and key found
	 */
	public function testCacheEnabledKeyFound()
	{
		$latte = TestUtils::createLatte();
		$latte->addProvider(AssetMacro::CONFIG_PROVIDER, [
			'cache' => true,
			'manifest' => WWW_FIXTURES_DIR . '/paths-manifest.json',
			'autodetect' => [],
			'wwwDir' => WWW_FIXTURES_DIR,
			'missingAsset' => 'notice',
			'missingManifest' => 'notice',
			'missingRevision' => 'notice',
		]);

		$cacheStorage = Mockery::mock(Nette\Caching\IStorage::class);
		$cacheStorage
			->shouldReceive('read')
			->once()
			->andReturn('/base/path/assets/CACHED/main.fc730c89c4255.js');

		$latte->addProvider('cacheStorage', $cacheStorage);

		$template = '{asset "assets/compiled/main.js"}';
		Assert::same(
			'/base/path/assets/CACHED/main.fc730c89c4255.js',
			$latte->renderToString($template, [
				'basePath' => '/base/path',
			])
		);
	}


	protected function tearDown()
	{
		parent::tearDown();
		Mockery::close();
	}
}

(new CacheTest())->run();
