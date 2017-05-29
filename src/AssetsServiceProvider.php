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

namespace Laradic\Assets;

use Illuminate\Contracts\Foundation\Application;
use Laradic\ServiceProvider\ServiceProvider;

/**
 * The main service provider
 *
 * @author        Laradic
 * @copyright     Copyright (c) 2015, Laradic
 * @license       https://tldrlegal.com/license/mit-license MIT
 * @package       Laradic\Assets
 */
class AssetsServiceProvider extends ServiceProvider
{
    protected $dir = __DIR__;

    protected $configFiles = [ 'laradic.assets' ];

    protected $bindings = [
        'laradic.assets.compiler'      => Compiler\Compiler::class,
    ];



    protected $singletons = [
        # 'laradic.assets.finder' => AssetFinder::class
    ];

    protected $aliases = [
        'laradic.assets'        => \Laradic\Assets\Contracts\Factory::class,
        'laradic.assets.finder' => AssetFinder::class,
    ];

    public function register()
    {

        $app = parent::register();
        $this->registerFinder();
        $this->registerFactory();
    }

    protected function registerFactory()
    {
        $this->app->singleton('laradic.assets', function (Application $app) {
            $config = $app[ 'config' ]->get('laradic.assets');

            /** @noinspection PhpParamsInspection */
            $factory = new Factory($app, $app[ 'files' ], $app[ 'url' ], $app[ 'laradic.assets.finder' ]);
            $factory->setCachePath($config[ 'cache_path' ]);
            $factory->setDebug($config[ 'debug' ]);
            $factory->setTypes($config[ 'types' ]);

            foreach ( $config[ 'filters' ] as $extension => $filters ) {
                foreach ( $filters as $filter ) {
                    $factory->addGlobalFilter($extension, $filter);
                }
            }

            return $factory;
        });
    }

    protected function registerFinder()
    {
        $this->app->singleton('laradic.assets.finder', function (Application $app) {
            /** @noinspection PhpParamsInspection */
            $finder = new AssetFinder($app[ 'files' ]);
            $paths  = $app[ 'config' ][ 'laradic.assets.asset_paths' ];
            $finder->setAssetPaths($paths);

            return $finder;
        });
    }
}
