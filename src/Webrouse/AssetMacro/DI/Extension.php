<?php

namespace Webrouse\AssetMacro\DI;

use Nette\DI\CompilerExtension;
use Nette\DI\Helpers;
use Webrouse\AssetMacro\AssetMacro;


class Extension extends CompilerExtension
{

	public function loadConfiguration()
	{
		$this->validateConfig([
			'versions' => AssetMacro::VERSIONS_AUTODETECT
		]);
	}


	public function beforeCompile()
	{
		$config = $this->getConfig();
		$builder = $this->getContainerBuilder();
		$builder->getDefinition('latte.latteFactory')
			->addSetup("?->addProvider(?, ?)", ['@self', AssetMacro::VERSIONS_PROVIDER, $config['versions']])
			->addSetup("?->addProvider(?, ?)", [
				'@self',
				AssetMacro::WWW_DIR_PROVIDER,
				Helpers::expand('%wwwDir%', $builder->parameters)
			])
			->addSetup("?->onCompile[] = function(\$engine) { " .
				AssetMacro::class . "::install(\$engine->getCompiler()); }",
				['@self']
			);
	}

}
