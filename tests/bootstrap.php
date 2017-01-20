<?php

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
@mkdir(TEMP_DIR, 0777, TRUE); // @ - base directory may already exist
register_shutdown_function(function () {
	Tester\Helpers::purge(TEMP_DIR);
	rmdir(TEMP_DIR);
});

$_SERVER = array_intersect_key($_SERVER, array_flip([
	'PHP_SELF', 'SCRIPT_NAME', 'SERVER_ADDR', 'SERVER_SOFTWARE', 'HTTP_HOST', 'DOCUMENT_ROOT', 'OS', 'argc', 'argv'
]));
$_SERVER['REQUEST_TIME'] = 1234567890;
$_GET = $_POST = [];

function run(Tester\TestCase $testCase) {
	$testCase->run();
}
