<?php

if (!($ncRoot = getenv('NEXTCLOUD_ROOT'))) {
    $ncRoot =  __DIR__ . '/../../..';
}

echo "Using ".realpath($ncRoot)." as Nextcloud root.\n\n";

require_once $ncRoot . '/lib/base.php';
require_once __DIR__ . '/../vendor/autoload.php';