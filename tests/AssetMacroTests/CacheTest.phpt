<?php
declare(strict_types=1);

namespace Webrouse\AssetMacro;

use Latte;
use Mockery;
use Nette;
use Tester\Assert;

include '../bootstrap.php';

class CacheTest extends TestCase
{
	/**
	 * Test cache storage missing
	 */
	public function testCacheStorageMissing()
	{
		$latte = $this->createLatte();
		Assert::same(
			'/base/path/fixtures/assets/compiled/main.fc730c89c4255.js',
			$latte->renderToString('{asset "assets/compiled/main.js"}')
		);
	}


	/**
	 * Test disabled cache
	 */
	public function testCacheDisabled()
	{
		$latte = $this->createLatte(['cache' => false]);

		// No method from cache storage could be called
		/** @var Nette\Caching\IStorage|Mockery\Mock $cacheStorage */
		$cacheStorage = Mockery::mock(Nette\Caching\IStorage::class);
		$latte->addProvider('cacheStorage', $cacheStorage);

		$template = '{asset "assets/compiled/main.js"}';
		Assert::same(
			'/base/path/fixtures/assets/compiled/main.fc730c89c4255.js',
			$latte->renderToString($template)
		);
	}


	/**
	 * Test cache enabled and key is missing
	 */
	public function testCacheEnabledKeyMissing()
	{
		$latte = $this->createLatte();

		/** @var Nette\Caching\IStorage|Mockery\Mock $cacheStorage */
		$cacheStorage = Mockery::mock(Nette\Caching\IStorage::class);
		$cacheStorage
			->shouldReceive('read')
			->once()
			->andReturn(null);
		$cacheStorage
			->shouldReceive('write')
			->with(Mockery::any(), '/base/path/fixtures/assets/compiled/main.fc730c89c4255.js', Mockery::any())
			->once()
			->andReturn(null);

		$latte->addProvider('cacheStorage', $cacheStorage);

		$template = '{asset "assets/compiled/main.js"}';
		Assert::same(
			'/base/path/fixtures/assets/compiled/main.fc730c89c4255.js',
			$latte->renderToString($template)
		);
	}


	/**
	 * Test cache enabled and key found
	 */
	public function testCacheEnabledKeyFound()
	{
		$latte = $this->createLatte();

		/** @var Nette\Caching\IStorage|Mockery\Mock $cacheStorage */
		$cacheStorage = Mockery::mock(Nette\Caching\IStorage::class);
		$cacheStorage
			->shouldReceive('read')
			->once()
			->andReturn('/base/path/fixtures/assets/CACHED/main.fc730c89c4255.js');

		$latte->addProvider('cacheStorage', $cacheStorage);

		$template = '{asset "assets/compiled/main.js"}';
		Assert::same(
			'/base/path/fixtures/assets/CACHED/main.fc730c89c4255.js',
			$latte->renderToString($template)
		);
	}


	protected function createLatte(array $config = []): Latte\Engine
	{
		return parent::createLatte(array_merge([
			'cache' => true,
			'manifest' => WWW_FIXTURES_DIR . '/paths-manifest.json',
			'autodetect' => [],
		], $config));
	}
}

(new CacheTest())->run();
