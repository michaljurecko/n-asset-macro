<?php
declare(strict_types=1);

namespace Webrouse\AssetMacro\DI;

use Nette\DI\CompilerExtension;
use Nette\DI\Definitions\FactoryDefinition;
use Nette\DI\Definitions\ServiceDefinition;
use Nette\DI\Helpers;
use Nette\Utils\Validators;
use Webrouse\AssetMacro\AssetMacro;
use Webrouse\AssetMacro\Config;
use Webrouse\AssetMacro\Exceptions\UnexpectedValueException;
use Webrouse\AssetMacro\ManifestService;
use Webrouse\AssetMacro\Utils;


class Extension extends CompilerExtension
{

	/**
	 * Default configuration
	 * @var array
	 */
	public const DEFAULTS = [
		// Cache generated output
		'cache' => '%productionMode%',
		// Assets revision manifest
		'manifest' => null,
		// Paths for manifest auto-detection
		'autodetect' => [
			'assets.json',
			'busters.json',
			'versions.json',
			'manifest.json',
			'rev-manifest.json',
		],
		// Absolute path to assets dir
		'assetsPath' => '%wwwDir%/',
		// Public path to "assetsPath"
		'publicPath' => '/',
		// Error handling (exception, notice, or ignore)
		'missingAsset' => 'notice',
		'missingManifest' => 'notice',
		'missingRevision' => 'notice',
		// Default format
		'format' => '%%url%%',
	];


	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();
		$config = Helpers::expand($this->validateConfig(self::DEFAULTS), $builder->parameters);

		// Validate types
		Validators::assertField($config, 'assetsPath', 'string');
		Validators::assertField($config, 'publicPath', 'string');
		Validators::assertField($config, 'manifest', 'null|string|array');
		Validators::assertField($config, 'autodetect', 'array');
		Validators::assertField($config, 'format', 'string');

		// Validate policies
		$choices = [Utils::MISSING_POLICY_IGNORE, Utils::MISSING_POLICY_NOTICE, Utils::MISSING_POLICY_EXCEPTION];
		$this->validatePolicy($config, 'missingAsset', $choices);
		$this->validatePolicy($config, 'missingManifest', $choices);
		$this->validatePolicy($config, 'missingRevision', $choices);

		// Config
		$builder
			->addDefinition($this->prefix('config'))
			->setFactory(Config::class, [$config]);

		// Manifest service
		$builder
			->addDefinition($this->prefix('manifest'))
			->setFactory(ManifestService::class, [$this->prefix('@config')]);
	}


	public function beforeCompile()
	{
		$builder = $this->getContainerBuilder();

		// Setup macro
		$latteDefinition = $builder->getDefinition('latte.latteFactory');

		// Compatibility with Nette 3.0
		if (class_exists(FactoryDefinition::class) && $latteDefinition instanceof FactoryDefinition) {
			$latteDefinition = $latteDefinition->getResultDefinition();
		}

		/** @var ServiceDefinition $latteDefinition */
		$latteDefinition
			->addSetup('?->addProvider(?, ?)', ['@self', AssetMacro::MANIFEST_PROVIDER, $this->prefix('@manifest')])
			->addSetup('?->onCompile[] = function($engine) { ' .
				AssetMacro::class . '::install($engine->getCompiler()); }',
				['@self']
			);
	}


	private function validatePolicy(array &$config, $key, array $choices): void
	{
		if (!in_array($config[$key], $choices, true)) {
			throw new UnexpectedValueException(sprintf(
				"Unexpected value '%s' of '%s' configuration key. Allowed values: %s.",
				$config[$key],
				$this->prefix($key),
				implode(', ', $choices)
			));
		}
	}
}
