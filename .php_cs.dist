<?php

declare(strict_types=1);

require_once './vendor/autoload.php';

use Nextcloud\CodingStandard\Config as NextcloudConfig;
use PhpCsFixer\Config;

$nextcloudConfig = new NextcloudConfig();
$config = new Config();

$rules = $nextcloudConfig->getRules();
$rules['ordered_imports'] = ['sort_algorithm' => 'alpha'];

$config
	->setIndent("\t")
	->setRules($rules)
	->getFinder()
	->ignoreVCSIgnored(true)
	->notPath('build')
	->notPath('l10n')
	->notPath('src')
	->notPath('vendor')
	->in(__DIR__);
return $config;