<?php

namespace Webrouse\AssetMacro;

use Nette;
use Nette\Bridges\ApplicationLatte\ILatteFactory;
use Tester;
use Tester\TestCase;
use Tester\Assert;

include '../bootstrap.php';

class EscapeTest extends TestCase
{
    /**
     * Test auto-escape of macro output path
     */
    public function testEscapePath() {
        $latte = TestUtils::createLatte();
        $latte->addProvider(AssetMacro::CONFIG_PROVIDER, [
            'cache' => false,
            'manifest' => [
                'assets/compiled/escape.js' => '"quotes"',
            ],
            'autodetect' => [],
            'wwwDir' => WWW_FIXTURES_DIR,
            'missingAsset' => 'exception',
            'missingManifest' => 'exception',
            'missingRevision' => 'exception',
        ]);

        $template = '<tag data-x="{asset "assets/compiled/escape.js"}"></tag>';
        Assert::same(
            '<tag data-x="/base/path/assets/compiled/escape.js?v=&quot;quotes&quot;"></tag>',
            $latte->renderToString($template, [
                'basePath' => '/base/path'
            ])
        );
    }

    /**
     * Test noescape filter - output path
     */
    public function testNoescapePath() {
        $latte = TestUtils::createLatte();
        $latte->addProvider(AssetMacro::CONFIG_PROVIDER, [
            'cache' => false,
            'manifest' => [
                'assets/compiled/escape.js' => '"quotes"',
            ],
            'autodetect' => [],
            'wwwDir' => WWW_FIXTURES_DIR,
            'missingAsset' => 'exception',
            'missingManifest' => 'exception',
            'missingRevision' => 'exception',
        ]);

        $template = '<tag data-x="{asset "assets/compiled/escape.js"|noescape}"></tag>';
        Assert::same(
            '<tag data-x="/base/path/assets/compiled/escape.js?v="quotes""></tag>',
            $latte->renderToString($template, [
                'basePath' => '/base/path'
            ])
        );
    }

    /**
     * Test auto-escape of macro output content
     */
    public function testEscapeContent() {
        $latte = TestUtils::createLatte();
        $latte->addProvider(AssetMacro::CONFIG_PROVIDER, [
            'cache' => false,
            'manifest' => [
                'assets/compiled/escape.js' => '"quotes"',
            ],
            'autodetect' => [],
            'wwwDir' => WWW_FIXTURES_DIR,
            'missingAsset' => 'exception',
            'missingManifest' => 'exception',
            'missingRevision' => 'exception',
        ]);

        $template = '<tag data-x="{asset "assets/compiled/escape.js", "%content%"}"></tag>';
        Assert::same(
            '<tag data-x="&quot;quotes content&quot;"></tag>',
            $latte->renderToString($template, [
                'basePath' => '/base/path'
            ])
        );
    }

    /**
     * Test noescape filter - output content
     */
    public function testNoescapeContent() {
        $latte = TestUtils::createLatte();
        $latte->addProvider(AssetMacro::CONFIG_PROVIDER, [
            'cache' => false,
            'manifest' => [
                'assets/compiled/escape.js' => '"quotes"',
            ],
            'autodetect' => [],
            'wwwDir' => WWW_FIXTURES_DIR,
            'missingAsset' => 'exception',
            'missingManifest' => 'exception',
            'missingRevision' => 'exception',
        ]);

        $template = '<tag data-x="{asset "assets/compiled/escape.js", "%content%"|noescape}"></tag>';
        Assert::same(
            '<tag data-x=""quotes content""></tag>',
            $latte->renderToString($template, [
                'basePath' => '/base/path'
            ])
        );
    }
}

(new EscapeTest())->run();
