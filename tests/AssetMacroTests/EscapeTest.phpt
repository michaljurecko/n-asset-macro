<?php
declare(strict_types=1);

namespace Webrouse\AssetMacro;

use Latte;
use Tester\Assert;

include '../bootstrap.php';

class EscapeTest extends TestCase
{
	/**
	 * Test auto-escape of macro output path
	 */
	public function testEscapePath()
	{
		$latte = $this->createLatte();
		Assert::same(
			'<tag data-x="/base/path/fixtures/assets/compiled/escape.js?v=&quot;quotes&quot;"></tag>',
			$latte->renderToString('<tag data-x="{asset "assets/compiled/escape.js"}"></tag>')
		);
	}


	/**
	 * Test noescape filter - output path
	 */
	public function testNoescapePath()
	{
		$latte = $this->createLatte();
		Assert::same(
			'<tag data-x="/base/path/fixtures/assets/compiled/escape.js?v="quotes""></tag>',
			$latte->renderToString('<tag data-x="{asset "assets/compiled/escape.js"|noescape}"></tag>')
		);
	}


	/**
	 * Test auto-escape of macro output content
	 */
	public function testEscapeContent()
	{
		$latte = $this->createLatte();
		Assert::same(
			'<tag data-x="&quot;quotes content&quot;"></tag>',
			$latte->renderToString('<tag data-x="{asset "assets/compiled/escape.js", "%content%"}"></tag>')
		);
	}


	/**
	 * Test noescape filter - output content
	 */
	public function testNoescapeContent()
	{
		$latte = $this->createLatte();
		Assert::same(
			'<tag data-x=""quotes content""></tag>',
			$latte->renderToString('<tag data-x="{asset "assets/compiled/escape.js", "%content%"|noescape}"></tag>')
		);
	}


	/**
	 * Test invalid identifier
	 * @throws Latte\CompileException
	 */
	public function testInvalidIdentifier()
	{
		$latte = $this->createLatte();
		$latte->renderToString('{asset "assets/compiled/main.js"|invalid}');
	}


	protected function createLatte(array $config = []): Latte\Engine
	{
		return parent::createLatte(array_merge($config, [
			'cache' => false,
			'manifest' => [
				'assets/compiled/escape.js' => '"quotes"',
			],
			'autodetect' => [],
			'missingAsset' => 'exception',
			'missingManifest' => 'exception',
			'missingRevision' => 'exception',
		]));
	}
}

(new EscapeTest())->run();
