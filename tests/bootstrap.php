<?php

if (!defined('PHPUNIT_RUN')) {
	define('PHPUNIT_RUN', 1);
}

if (!($ncRoot = getenv('NEXTCLOUD_ROOT'))) {
	$ncRoot = __DIR__ . '/../../..';
}

require_once $ncRoot . '/lib/base.php';
require_once __DIR__ . '/../vendor/autoload.php';

\OC_App::loadApp('bbb');

if (!class_exists('\PHPUnit\Framework\TestCase')) {
	require_once('PHPUnit/Autoload.php');
}

echo "Using ".realpath($ncRoot)." as Nextcloud root.\n\n";
