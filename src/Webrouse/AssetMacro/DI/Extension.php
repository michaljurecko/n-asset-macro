<?php
declare(strict_types=1);

namespace Webrouse\AssetMacro\DI;

use Nette\DI\CompilerExtension;
use Nette\DI\Definitions\FactoryDefinition;
use Nette\DI\Helpers;
use Nette\DI\ServiceDefinition;
use Nette\Utils\Validators;
use Webrouse\AssetMacro\AssetMacro;
use Webrouse\AssetMacro\Exceptions\UnexpectedValueException;


class Extension extends CompilerExtension
{

	/**
	 * Default configuration
	 * @var array
	 */
	public $defaults = [
		// Cache generated output
		'cache' => '%productionMode%',
		// Public www dir
		'wwwDir' => '%wwwDir%',
		// Assets revision manifest
		'manifest' => null,
		// Paths for manifest autodetection
		'autodetect' => [
			'assets.json',
			'busters.json',
			'versions.json',
			'manifest.json',
			'rev-manifest.json',
		],
		// Error handling (exception, notice, or ignore)
		'missingAsset' => 'notice',
		'missingManifest' => 'notice',
		'missingRevision' => 'notice',
		// Default format
		'format' => '%%url%%',
	];


	/**
	 * @throws \Nette\Utils\AssertionException
	 */
	public function beforeCompile()
	{
		$builder = $this->getContainerBuilder();
		$config = Helpers::expand($this->validateConfig($this->defaults), $builder->parameters);

		// Validate configuration
		Validators::assertField($config, 'wwwDir', 'string');
		Validators::assertField($config, 'manifest', 'null|string|array');
		Validators::assertField($config, 'autodetect', 'array');
		Validators::assertField($config, 'format', 'string');
		$choices = ['exception', 'notice', 'ignore'];
		$this->validateChoices('missingAsset', $choices);
		$this->validateChoices('missingManifest', $choices);
		$this->validateChoices('missingRevision', $choices);

		// Setup macro
		$latteDefinition = $builder->getDefinition('latte.latteFactory');

		// Compatibility with Nette 3.0
		if (class_exists(FactoryDefinition::class) && $latteDefinition instanceof FactoryDefinition) {
			$latteDefinition = $latteDefinition->getResultDefinition();
		}

		/** @var ServiceDefinition $latteDefinition */
		$latteDefinition
			->addSetup('?->addProvider(?, ?)', ['@self', AssetMacro::CONFIG_PROVIDER, $config])
			->addSetup('?->onCompile[] = function($engine) { ' .
				AssetMacro::class . '::install($engine->getCompiler()); }',
				['@self']
			);
	}


	/**
	 * @param string $key
	 * @param array $choices
	 */
	private function validateChoices($key, array $choices)
	{
		if (!in_array($this->config[$key], $choices, true)) {
			throw new UnexpectedValueException(sprintf(
				"Unexpected value '%s' of '%s' configuration key. Allowed values: %s.",
				$this->config[$key],
				$this->prefix($key),
				implode(', ', $choices)
			));
		}
	}
}
