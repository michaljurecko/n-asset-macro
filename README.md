# Webrouse/n-asset-macro

[![Build Status](https://img.shields.io/travis/webrouse/n-asset-macro.svg?style=flat-square)](https://travis-ci.org/webrouse/n-asset-macro)
[![Quality Score](https://img.shields.io/scrutinizer/g/webrouse/n-asset-macro.svg?style=flat-square)](https://scrutinizer-ci.com/g/webrouse/n-asset-macro/)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/webrouse/n-asset-macro.svg?style=flat-square)](https://scrutinizer-ci.com/g/webrouse/n-asset-macro/)
[![Latest stable](https://img.shields.io/packagist/v/webrouse/n-asset-macro.svg?style=flat-square)](https://packagist.org/packages/webrouse/n-asset-macro)

Asset macro for Latte and Nette Framework
useful for assets cache busting with gulp-buster and other similar tools.

Cache busting allows the assets to have a large expiry time in the browsers cache.

If the asset is changed then is also changed the hash in url and the browser is forced to retrieve the new version, otherwise is used local cached version.


## Installation

The best way to install **webrouse/n-asset-macro** is using  [Composer](http://getcomposer.org/):

```sh
$ composer require webrouse/n-asset-macro
```

Register the extension:
```yaml
# app/config/config.neon
extensions:
    assetMacro: Webrouse\AssetMacro\DI\Extension
```

## Usage

Use in any template:
```latte
{* app/presenters/templates/@layout.latte *}
<script src="{asset resources/compiled/vendor.js}"></script>
<script src="{asset resources/compiled/main.js}"></script>
```

Asset macro prepend path with ```$basePath``` and append version for cache busting
(eg. ```?v=32ecae4b82```).

### Asset version

Asset processors (such as gulp, bower, ...) can be usually set to generate JSON file
with assets versions based on their actual content.

Asset macro searches for the versions on several paths:
* asset path with appended `.json` extension (eg. `vendor.js.json`)
* `busters.json`, `versions.json` or `rev-manifest.json`
in asset directory or in any parent directory up to `%wwwDir%`

Specific path may be set in the configuration:
```yaml
# app/config/config.neon
assetMacro:
    versions: %tempDir%/assets.json
```

Or you can set the array of versions:
```yaml
# app/config/config.neon
assetMacro:
    versions:
      'resources/compiled/vendor.js': 16016edc74d
      'main.js':  4b82916016
```

If the file with versions can not be found an exception is thrown.

If the file exists, but it hasn't record for the asset the result path is `...?v=unknown`.


