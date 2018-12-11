<?php
declare(strict_types=1);

namespace Webrouse\AssetMacro;

interface IFormatter
{
	public function format(Asset $asset, string $format = '%url%', bool $absolute = false): string;
}
