<?php

namespace Webrouse\AssetMacro;

use Nette\Utils\Strings;
use Webrouse\AssetMacro\Exceptions\InvalidPathException;


class Utils
{

	/**
	 * @param string $path
	 * @return string
	 */
	public static function normalizePath($path)
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


	/**
	 * Throw exception, trigger error or ignore according of action
	 * @param \Exception $e
	 * @param string $action
	 * @param bool $need
	 * @throws \Exception
	 */
	public static function throwError(\Exception $e, $action = 'exception', $need = TRUE)
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
