<?php
declare(strict_types=1);
if (@!include __DIR__ . '/../vendor/autoload.php') {
	echo 'Install Nette Tester using `composer install`';
	exit(1);
}

// configure environment
Tester\Environment::setup();
date_default_timezone_set('Europe/Bratislava');

// create temporary directory
define('TESTS_DIR', __DIR__);
define('FIXTURES_DIR', TESTS_DIR . '/fixtures');
define('TEMP_DIR', TESTS_DIR . '/tmp/' . \Nette\Utils\Random::generate(30));
define('WWW_FIXTURES_DIR', $wwwDir = FIXTURES_DIR . '/www');
@mkdir(TEMP_DIR, 0777, true); // @ - base directory may already exist
register_shutdown_function(function () {
	Tester\Helpers::purge(TEMP_DIR);
	rmdir(TEMP_DIR);
});

include_once 'TestUtils.php';
include_once 'TestCase.php';
