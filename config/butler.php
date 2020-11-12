<?php

return [

    'graphql' => [

        'include_debug_message' => env('BUTLER_GRAPHQL_INCLUDE_DEBUG_MESSAGE', env('APP_DEBUG', false)),
        'include_trace' => env('BUTLER_GRAPHQL_INCLUDE_TRACE', env('APP_DEBUG', false)),

        'namespace' => env('BUTLER_GRAPHQL_NAMESPACE', 'App\\Http\\Graphql\\'),

        'schema' => env('BUTLER_GRAPHQL_SCHEMA', base_path('app/Http/Graphql/schema.graphql')),

    ],

];
