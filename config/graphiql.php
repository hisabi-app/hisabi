<?php declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Routes configuration
    |--------------------------------------------------------------------------
    |
    | Set the key as URI at which the GraphiQL UI can be viewed,
    | and add any additional configuration for the route.
    |
    | You can add multiple routes pointing to different GraphQL endpoints.
    |
    */

    'routes' => [
        '/graphiql' => [
            'name' => 'graphiql',
             'middleware' => ['web'],
            // 'prefix' => '',
            // 'domain' => 'graphql.' . env('APP_DOMAIN', 'localhost'),

            /*
            |--------------------------------------------------------------------------
            | Default GraphQL endpoint
            |--------------------------------------------------------------------------
            |
            | The default endpoint that the GraphiQL UI is set to.
            | It assumes you are running GraphQL on the same domain
            | as GraphiQL, but can be set to any URL.
            |
            */

            'endpoint' => '/graphql',

            /*
            |--------------------------------------------------------------------------
            | Subscription endpoint
            |--------------------------------------------------------------------------
            |
            | The default subscription endpoint the GraphiQL UI uses to connect to.
            | Tries to connect to the `endpoint` value if `null` as ws://{{endpoint}}
            |
            | Example: `ws://your-endpoint` or `wss://your-endpoint`
            |
            */

            'subscription-endpoint' => env('GRAPHIQL_SUBSCRIPTION_ENDPOINT', null),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Control GraphiQL availability
    |--------------------------------------------------------------------------
    |
    | Control if the GraphiQL UI is accessible at all.
    | This allows you to disable it in certain environments,
    | for example you might not want it active in production.
    |
    */

    'enabled' => env('GRAPHIQL_ENABLED', false),
];
