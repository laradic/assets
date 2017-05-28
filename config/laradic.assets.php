<?php
/**
 * Part of the Laradic PHP Packages.
 *
 * Copyright (c) 2017. Robin Radic.
 *
 * The license can be found in the package and online at https://laradic.mit-license.org.
 *
 * @copyright Copyright 2017 (c) Robin Radic
 * @license https://laradic.mit-license.org The MIT License
 */

return [
    'debug'       => null, // if null, app.debug will be used

    // paths relative to public folder
    'asset_paths' => [ 'assets' ],
    'cache_path'  => 'cache/assets',

    'types' => [
        'style'  => [ 'css', 'scss', 'sass', 'less' ],
        'script' => [ 'js', 'ts', 'cs' ],
        'image'  => [ 'jpg', 'png', 'jpeg', 'gif', 'bmp' ],
    ],

    'filters' => [
        'css'  => [
            #  Assetic\Filter\CssImportFilter::class,
            Laradic\Assets\Filters\UriRewriteFilter::class,
            Assetic\Filter\CssMinFilter::class
        ],
        'scss' => [
            # Assetic\Filter\ScssphpFilter::class,
            Laradic\Assets\Filters\UriRewriteFilter::class,
        ],

        'js' => [
            Assetic\Filter\JSMinFilter::class,
        ],
    ],
];
