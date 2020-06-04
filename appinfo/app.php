<?php

OCP\Util::addScript ( 'bbb', 'filelist');

$apiUrl = \OC::$server->getConfig()->getAppValue('bbb', 'api.url');
$parsedApiUrl = @parse_url($apiUrl);

if ($parsedApiUrl !== false) {
    $manager = \OC::$server->getContentSecurityPolicyManager();
    $policy = new \OCP\AppFramework\Http\EmptyContentSecurityPolicy();

	$policy->addAllowedFormActionDomain(($parsedApiUrl['scheme'] ?: 'https') . '://' . $parsedApiUrl['host']);

    $manager->addDefaultPolicy($policy);
}