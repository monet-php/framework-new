<?php

return [
    'not_found' => 'Theme could not be found',

    'manifest_not_found' => 'Theme manifest could not be found',

    'invalid_parent' => 'Theme has an invalid parent theme',

    'installer' => [
        'invalid_manifest' => 'Theme manifest is invalid',

        'already_installed' => 'Theme is already installed',

        'invalid_paths_config' => 'Paths configuration is invalid',

        'extraction_failed' => 'Failed to extract theme',

        'success' => [
            'title' => 'Theme installed successfully',

            'body' => ':name'
        ],

        'failed' => [
            'title' => 'Theme install failed',

            'body' => 'An unknown error occurred whilst installing theme'
        ],
    ],

    'enable' => [
        'success' => [
            'title' => 'Theme enabled successfully',

            'body' => ':name'
        ],

        'failed' => [
            'title' => 'Theme enable failed'
        ]
    ],

    'disable' => [
        'success' => [
            'title' => 'Theme disabled successfully',

            'body' => ':name'
        ],

        'failed' => [
            'title' => 'Theme disable failed'
        ]
    ],

    'delete' => [
        'success' => [
            'title' => 'Theme deleted successfully',

            'body' => ':name'
        ],

        'failed' => [
            'title' => 'Theme could not be deleted'
        ]
    ],

    'publish' => [
        'success' => [
            'title' => 'Theme published successfully',

            'body' => ':name'
        ],

        'failed' => [
            'title' => 'Theme publish failed',

            'body' => 'Theme assets could not be published'
        ]
    ],

    'boot' => [
        'failed' => [
            'title' => 'Theme failed to load',

            'body' => ':name has been disabled'
        ]
    ],
];
