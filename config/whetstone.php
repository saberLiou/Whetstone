<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default roots and namespaces for the stub classes
    |--------------------------------------------------------------------------
    | Stub Classes will be carved by their root and namespace into their own directory.
    */

    'roots' => [
        'helper' => 'App',
        'repository' => 'App',
        'service' => 'App',
        'presenter' => 'App',
        'view_composer' => 'App',
    ],

    'namespaces' => [
        'helper' => 'Helpers',
        'repository' => 'Repositories',
        'service' => 'Services',
        'presenter' => 'Presenters',
        'view_composer' => 'Http\ViewComposers',
    ],

];