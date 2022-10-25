<?php

return [
    'not_found' => 'Module could not be found',

    'manifest_not_found' => 'Module manifest could not be found',

    'invalid_dependency' => 'Module has an invalid dependency',

    'installer' => [
        'invalid_manifest' => 'Module manifest is invalid',

        'already_installed' => 'Module is already installed',

        'invalid_paths_config' => 'Paths configuration is invalid',

        'extraction_failed' => 'Failed to extract module',

        'success' => [
            'title' => 'Module installed successfully',

            'body' => ':name'
        ],

        'failed' => [
            'title' => 'Module install failed',

            'body' => 'An unknown error occurred whilst installing module'
        ],
    ],

    'enable' => [
        'success' => [
            'title' => 'Module enabled successfully',

            'body' => ':name'
        ],

        'failed' => [
            'title' => 'Module enable failed'
        ]
    ],

    'disable' => [
        'success' => [
            'title' => 'Module disabled successfully',

            'body' => ':name'
        ],

        'failed' => [
            'title' => 'Module disable failed'
        ]
    ],

    'delete' => [
        'success' => [
            'title' => 'Module deleted successfully',

            'body' => ':name'
        ],

        'failed' => [
            'title' => 'Module could not be deleted'
        ]
    ],

    'publish' => [
        'success' => [
            'title' => 'Module published successfully',

            'body' => ':name'
        ],

        'failed' => [
            'title' => 'Module publish failed',

            'body' => 'Module assets could not be published'
        ]
    ],

    'boot' => [
        'failed' => [
            'title' => 'Module failed to load',

            'body' => ':name has been disabled'
        ]
    ],
];
