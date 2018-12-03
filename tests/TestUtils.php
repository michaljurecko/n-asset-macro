<?php
declare(strict_types=1);

namespace Webrouse\AssetMacro;

use Latte;

class TestUtils
{
	/**
	 * @return Latte\Engine
	 */
	public static function createLatte()
	{
		$latte = new Latte\Engine;
		$latte->setLoader(new Latte\Loaders\StringLoader);
		$latte->setTempDirectory(TEMP_DIR);
		$latte->onCompile[] = function (Latte\Engine $engine) {
			AssetMacro::install($engine->getCompiler());
		};

		return $latte;
	}
}
