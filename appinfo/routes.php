<?php

return [
	'resources' => [
		'room' => ['url' => '/rooms'],
		'roomShare' => ['url' => '/roomShares'],
		'restriction' => ['url' => '/restrictions'],
	],
	'routes' => [
		['name' => 'page#index', 'url' => '/', 'verb' => 'GET'],
		['name' => 'server#records', 'url' => '/server/{roomUid}/records', 'verb' => 'GET'],
		['name' => 'server#check', 'url' => '/server/check', 'verb' => 'POST'],
		['name' => 'server#version', 'url' => '/server/version', 'verb' => 'GET'],
		['name' => 'server#delete_record', 'url' => '/server/record/{recordId}', 'verb' => 'DELETE'],
		['name' => 'join#index', 'url' => '/b/{token}', 'verb' => 'GET'],
		['name' => 'restriction#user', 'url' => '/restrictions/user', 'verb' => 'GET'],
	]
];
