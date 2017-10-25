<?php
/*
|--------------------------------------------------------------------------
| Repository Config
|--------------------------------------------------------------------------
|
|
*/
return [

    /*
    |--------------------------------------------------------------------------
    | Cache Config
    |--------------------------------------------------------------------------
    |
    */
    'cache'      => [
        /*
         |--------------------------------------------------------------------------
         | Cache Status
         |--------------------------------------------------------------------------
         |
         | Enable or disable cache
         |
         */
        'enabled'    => env('REPOSITORY_CACHE_ENABLED', false),

        /*
         |--------------------------------------------------------------------------
         | Cache Minutes
         |--------------------------------------------------------------------------
         |
         | Time of expiration cache
         |
         */
        'minutes'    => env('REPOSITORY_CACHE_EXPIRY', 30),

        /*
         |--------------------------------------------------------------------------
         | Cache Repository
         |--------------------------------------------------------------------------
         |
         | Instance of Illuminate\Contracts\Cache\Repository
         |
         */
        'repository' => 'cache',

        /*
          |--------------------------------------------------------------------------
          | Cache Clean Listener
          |--------------------------------------------------------------------------
          |
          |
          |
          */
        'clean'      => [

            /*
              |--------------------------------------------------------------------------
              | Enable clear cache on repository changes
              |--------------------------------------------------------------------------
              |
              */
            'enabled' => true,

            /*
              |--------------------------------------------------------------------------
              | Enable clear cache on repository changes
              |--------------------------------------------------------------------------
              |
            */
            'redis' => env('REPOSITORY_REDIS_CLEAN', false),

            /*
              |--------------------------------------------------------------------------
              | Actions in Repository
              |--------------------------------------------------------------------------
              |
              | create : Clear Cache on create Entry in repository
              | update : Clear Cache on update Entry in repository
              | delete : Clear Cache on delete Entry in repository
              |
              */
            'on'      => [
                'create' => true,
                'update' => true,
                'delete' => true,
                'flush' => true,
            ]
        ],

        'params'     => [
            /*
            |--------------------------------------------------------------------------
            | Skip Cache Params
            |--------------------------------------------------------------------------
            |
            |
            */
            'skipCache' => 'skipCache'
        ],

        /*
       |--------------------------------------------------------------------------
       | Methods Allowed
       |--------------------------------------------------------------------------
       |
       | methods cacheable : all, find, findWhere
       |
       */
        'allowed'    => [
            'only'   => null,
            'except' => null
        ]
    ]

];