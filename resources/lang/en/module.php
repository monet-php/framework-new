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

        'failed' => [
            'title' => 'Module install failed',

            'body' => 'An unknown error occurred whilst installing module'
        ],

        'success' => [
            'title' => 'Module installed successfully',

            'body' => ''
        ],

        'publish' => [
            'success' => [
                'title' => 'Module published successfully'
            ],

            'failed' => [
                'title' => 'Module publish failed',

                'body' => 'Module assets could not be published'
            ]
        ],
    ],

    'enable' => [
        'success' => [
            'title' => 'Module enabled successfully'
        ],

        'failed' => [
            'title' => 'Module enable failed'
        ]
    ],

    'disable' => [
        'success' => [
            'title' => 'Module disabled successfully'
        ],

        'failed' => [
            'title' => 'Module disable failed'
        ]
    ],

    'delete' => [
        'success' => [
            'title' => 'Module deleted successfully'
        ],

        'failed' => [
            'title' => 'Module could not be deleted'
        ]
    ],

    'boot' => [
        'failed' => [
            'title' => 'Module failed to load',

            'body' => ':module has been disabled'
        ]
    ],
];
