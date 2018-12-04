<?php
declare(strict_types=1);

namespace Webrouse\AssetMacro;

use Nette\Utils\Strings;
use Webrouse\AssetMacro\Exceptions\AssetMacroException;
use Webrouse\AssetMacro\Exceptions\InvalidPathException;


class Utils
{
	public const
		MISSING_POLICY_IGNORE = 'ignore',
		MISSING_POLICY_NOTICE = 'notice',
		MISSING_POLICY_EXCEPTION = 'exception';


	public static function normalizePath(string $path): string
	{
		// Remove any kind of unicode whitespace
		$normalized = preg_replace('#\p{C}+|^\./#u', '', $path);

		// Path remove self referring paths ("/./").
		$normalized = preg_replace('#/\.(?=/)|^\./|\./$#', '', $normalized);

		// Regex for resolving relative paths
		$regex = '#\/*[^/\.]+/\.\.#Uu';

		while (preg_match($regex, $normalized)) {
			$normalized = preg_replace($regex, '', $normalized);
		}

		if (preg_match('#/\.{2}|\.{2}/#', $normalized)) {
			throw new InvalidPathException(
				sprintf("Path is outside of the defined root, path: '%s', resolved: '%s'.", $path, $normalized)
			);
		}

		$normalized = trim($normalized, '\\/');

		return Strings::match($path, '~^\\/~') ? ('/' . $normalized) : $normalized;
	}


	public static function throwError(AssetMacroException $e, $action = 'exception', bool $need = true): void
	{
		if ($need) {
			if ($action === 'exception') {
				throw $e;

			} elseif ($action === 'notice') {
				trigger_error($e->getMessage(), E_USER_NOTICE);
			}
		}
	}
}
