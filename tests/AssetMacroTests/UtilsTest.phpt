<?php

namespace Webrouse\AssetMacro;

use Latte;
use Tester\TestCase;
use Tester\Assert;

include '../bootstrap.php';

/**
 * @testCase
 */
class UtilsTest extends TestCase {
	public function testNormalizeAbsolutePath() {
		Assert::equal("/abc", Utils::normalizePath("/abc"));
	}

	public function testNormalizeRelativePath() {
		Assert::equal("abc", Utils::normalizePath("abc"));
	}

	public function testNormalizeAbsolutePathDots() {
		Assert::equal("/abc/y", Utils::normalizePath("/test/../abc/x/../y"));
	}

	public function testNormalizeRelativePathDots() {
		Assert::equal("abc", Utils::normalizePath("test/../abc"));
	}

	/**
	 * @throws \Webrouse\AssetMacro\Exceptions\InvalidPathException
	 */
	public function testNormalizePathOutsideRoot() {
		Utils::normalizePath("test/../../abc");
	}
}

(new UtilsTest())->run();
