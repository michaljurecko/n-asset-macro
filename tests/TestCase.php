<?php
declare(strict_types=1);

namespace Webrouse\AssetMacro;

use Mockery;
use Tester\TestCase as TesterTestCase;

class TestCase extends TesterTestCase
{
	public const LATTE_VARS = [
		'basePath' => '/base/path',
		'baseUrl' => 'http://www.example.com/base/path',
	];


	protected function tearDown()
	{
		parent::tearDown();
		Mockery::close();
	}
}
