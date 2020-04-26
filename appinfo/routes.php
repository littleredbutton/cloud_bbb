<?php
return [
    'resources' => [
        'room' => ['url' => '/rooms'],
        'room_api' => ['url' => '/api/0.1/rooms']
    ],
    'routes' => [
        ['name' => 'join#index', 'url' => '/b/{token}', 'verb' => 'GET'],
        ['name' => 'room_api#preflighted_cors', 'url' => '/api/0.1/{path}',
         'verb' => 'OPTIONS', 'requirements' => ['path' => '.+']]
    ]
];