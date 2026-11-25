<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default roots and namespaces for the stub classes
    |--------------------------------------------------------------------------
    | Stub Classes will be carved by their root and namespace into their own directory,
    | which is resolved by the PSR-4 autoload map of the application's composer.json.
    |
    | A null root means the application namespace (e.g. "App"),
    | and an empty namespace carves the classes directly under the root.
    */

    'roots' => [
        'helper' => null,
        'repository' => null,
        'service' => null,
        'presenter' => null,
        'view_composer' => null,
    ],

    'namespaces' => [
        'helper' => 'Helpers',
        'repository' => 'Repositories',
        'service' => 'Services',
        'presenter' => 'Presenters',
        'view_composer' => 'Http\ViewComposers',
    ],

];