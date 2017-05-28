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
    // if true, assets will not be concenated and filters will be ignored resulting in  a <script> or <link> for each asset pointing to the original location.
    // if null, app.debug value will be used
    // can be set during runtime (not recommended) aswell using Asset::setDebug($val)
    'debug'       => null,

    // asset_paths are relative to the public folder
    'asset_paths' => [ 'assets' ],

    // cache_path is relative to public folder
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
