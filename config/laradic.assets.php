<?php

return [
    'debug' => null, // if null, app.debug will be used

    // paths relative to public folder
    'asset_paths'      => [ 'assets' ],
    'cache_path'       => 'cache/assets',

    'types' => [
        'style'  => [ 'css', 'scss', 'sass', 'less' ],
        'script' => [ 'js', 'ts', 'cs' ],
        'image'  => [ 'jpg', 'png', 'jpeg', 'gif', 'bmp' ]
    ],

    'filters' => [
        'css'  => [
          #  Assetic\Filter\CssImportFilter::class,
            Laradic\Assets\Filters\UriRewriteFilter::class,
        ],
        'scss' => [
           # Assetic\Filter\ScssphpFilter::class,
            Laradic\Assets\Filters\UriRewriteFilter::class,
        ],

        'js' => [
           # Assetic\Filter\JSMinFilter::class
        ]
    ]
];
