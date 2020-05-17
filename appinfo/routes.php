<?php
return [
    'resources' => [
        'room' => ['url' => '/rooms'],
        'room_api' => ['url' => '/api/0.1/rooms'],
    ],
    'routes' => [
        ['name' => 'server#records', 'url' => '/server/{roomUid}/records', 'verb' => 'GET'],
        ['name' => 'server#check', 'url' => '/server/check', 'verb' => 'POST'],
        ['name' => 'server#version', 'url' => '/server/version', 'verb' => 'GET'],
        ['name' => 'server#delete_record', 'url' => '/server/record/{recordId}', 'verb' => 'DELETE'],
        ['name' => 'join#index', 'url' => '/b/{token}', 'verb' => 'GET'],
        ['name' => 'room_api#preflighted_cors', 'url' => '/api/0.1/{path}',
         'verb' => 'OPTIONS', 'requirements' => ['path' => '.+']]
    ]
];