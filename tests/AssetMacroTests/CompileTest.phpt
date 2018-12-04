<?php
declare(strict_types=1);

namespace Webrouse\AssetMacro;

use Tester\Assert;

include '../bootstrap.php';

/**
 * @testCase
 */
class CompileTest extends TestCase
{

	/**
	 * Test compilation of AssetMacro
	 */
	public function testCompile()
	{
		$latte = TestUtils::createLatte();

		// main.js
		$template1 = '{asset "assets/compiled/main.js"}';
		Assert::contains(
			AssetMacro::class . '::getOutput("assets/compiled/main.js", [], $basePath, $baseUrl, $this->global->' . AssetMacro::CONFIG_PROVIDER . ', isset($this->global->cacheStorage) ? $this->global->cacheStorage : null)',
			$latte->compile($template1)
		);

		// assets/compiled/main.css
		$template2 = '{asset "assets/compiled/main.css"}';
		Assert::contains(
			AssetMacro::class . '::getOutput("assets/compiled/main.css", [], $basePath, $baseUrl, $this->global->' . AssetMacro::CONFIG_PROVIDER . ', isset($this->global->cacheStorage) ? $this->global->cacheStorage : null)',
			$latte->compile($template2)
		);
	}
}

(new CompileTest())->run();
