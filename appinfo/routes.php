<?php

return [
	'resources' => [
		'room' => ['url' => '/rooms'],
		'roomShare' => ['url' => '/roomShares'],
		'restriction' => ['url' => '/restrictions'],
	],
	'routes' => [
		['name' => 'page#index', 'url' => '/', 'verb' => 'GET'],
		['name' => 'server#isRunning', 'url' => '/server/{roomUid}/isRunning', 'verb' => 'GET'],
		['name' => 'server#insertDocument', 'url' => '/server/{roomUid}/insertDocument', 'verb' => 'POST'],
		['name' => 'server#records', 'url' => '/server/{roomUid}/records', 'verb' => 'GET'],
		['name' => 'server#check', 'url' => '/server/check', 'verb' => 'POST'],
		['name' => 'server#version', 'url' => '/server/version', 'verb' => 'GET'],
		['name' => 'server#delete_record', 'url' => '/server/record/{recordId}', 'verb' => 'DELETE'],
		['name' => 'server#publish_record', 'url' => '/server/record/{recordId}/publish', 'verb' => 'POST'],
		['name' => 'join#index', 'url' => '/b/{token}/{moderatorToken}', 'verb' => 'GET', 'defaults' => ['moderatorToken' => '']],
		['name' => 'restriction#user', 'url' => '/restrictions/user', 'verb' => 'GET'],
		['name' => 'hook#meetingEnded', 'url' => '/hook/ended/{token}/{mac}', 'verb' => 'GET'],
		['name' => 'hook#recordingReady', 'url' => '/hook/recording/{token}/{mac}', 'verb' => 'POST'],
	]
];
