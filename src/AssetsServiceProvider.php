<?php

namespace Laradic\Assets;

use Illuminate\Contracts\Foundation\Application;
use Laradic\Support\ServiceProvider;

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
        'laradic.assets.asset'         => Assetic\Asset::class,
        'laradic.assets.collection'    => Assetic\AssetCollection::class,
        'laradic.assets.builder.area'  => Builder\Area::class,
        'laradic.assets.builder.group' => Builder\Group::class,
        'laradic.assets.compiler'      => Compiler\Compiler::class,
    ];

    protected $singletons = [
        # 'laradic.assets.finder' => AssetFinder::class
    ];

    protected $aliases = [
        'laradic.assets'        => \Laradic\Contracts\Assets\Factory::class,
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
