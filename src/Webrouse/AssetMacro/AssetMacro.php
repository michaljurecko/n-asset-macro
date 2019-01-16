<?php
declare(strict_types=1);

namespace Webrouse\AssetMacro;

use Latte;
use Latte\Macros\MacroSet;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use Nette\Utils\Strings;
use Nette\Utils\Validators;

class AssetMacro extends MacroSet
{

	// Name of the Latte provider for manifest service
	public const MANIFEST_PROVIDER = 'assetMacroManifestService';


	public static function install(Latte\Compiler $compiler): void
	{
		$me = new self($compiler);
		$me->addMacro('asset', [$me, 'macroAsset']);
	}


	public function macroAsset(Latte\MacroNode $node, Latte\PhpWriter $writer): string
	{
		if ($node->modifiers && $node->modifiers !== '|noescape') {
			throw new Latte\CompileException('Only \'noescape\' modifier is allowed in ' . $node->getNotation());
		}

		// Validate arguments count
		$args = trim($node->args);
		$argsCount = $args === '' ? 0 : (substr_count($args, ',') + 1);
		if ($argsCount === 0) {
			throw new Latte\CompileException('Asset macro requires at least one argument.');
		} elseif ($argsCount > 4) {
			throw new Latte\CompileException('Asset macro must have no more than 4 arguments.');
		}

		return $writer->write(
			'echo ' . ($node->modifiers !== '|noescape' ? '%escape' : '') .
			'(' . self::class . '::getOutput(' .
			'%node.word, ' .
			'%node.array, ' .
			'$baseUrl ?? null, ' .
			'$this->global->' . self::MANIFEST_PROVIDER . ', ' .
			'$this->global->cacheStorage ?? null))');
	}


	public static function getOutput(string $asset, array $args, ?string $baseUrl, ManifestService $manifestService, IStorage $storage = null): string
	{
		$config = $manifestService->getConfig();

		// Cache
		$cacheKey = md5(implode(';', [$asset, serialize($args), $config->getHash(), $baseUrl ?? '']));
		$cache = ($config->isCacheEnabled() && $storage) ? new Cache($storage, 'Webrouse.AssetMacro') : null;

		// Load cached value
		if ($cache && ($output = $cache->load($cacheKey)) !== null) {
			return (string) $output;
		}

		// Generate output and store value to cache
		$output = self::generateOutput($asset, $args, $config, $manifestService);
		if ($cache) {
			$cache->save($cacheKey, $output);
		}

		return $output;
	}


	public static function generateOutput(string $asset, array $args, Config $config, ManifestService $manifestService): ?string
	{
		[$format, $needed, $absolute] = self::processArguments($asset, $args, $config);
		return $manifestService->format($asset, $needed, $format, $absolute);
	}


	private static function processArguments(string $asset, array $args, Config $config): array
	{
		$format = $args['format'] ?? ($args[0] ?? $config->getDefaultFormat());
		$needed = $args['need'] ?? ($args[1] ?? true);
		$absolute = $args['absolute'] ?? ($args[2] ?? false);

		Validators::assert($asset, 'string', 'path');
		Validators::assert($format, 'string', 'format');
		Validators::assert($needed, 'bool', 'need');
		Validators::assert($needed, 'bool', 'absolute');

		if (Strings::startsWith($asset, '//')) {
			$absolute = true;
		}

		return [$format, $needed, $absolute];
	}
}
