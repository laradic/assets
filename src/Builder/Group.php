<?php
/**
 * Part of the Laradic PHP packages.
 *
 * License and copyright information bundled with this package in the LICENSE file
 */
namespace Laradic\Assets\Builder;

use Assetic\Filter\HashableInterface;
use Closure;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Contracts\Container\Container;
use InvalidArgumentException;
use Laradic\Assets\Assetic\AssetInterface;
use Laradic\Contracts\Assets\Factory as FactoryContract;
use Laradic\Contracts\Dependencies\Dependable;
use Laradic\Dependencies\Sorter;
use Laradic\Filesystem\Filesystem;
use Laradic\Support\Str;

/**
 * This is the class AssetGroup.
 *
 * @package        Laradic\Themes
 * @author         Laradic
 * @copyright      Copyright (c) 2015, Laradic. All rights reserved
 */
class Group implements Dependable, BuilderInterface
{

    /**
     * The unique id for this group
     *
     * @var string
     */
    protected $id;

    /**
     * The instance of Area where this group belongs in
     *
     * @var \Laradic\Assets\Builder\Area
     */
    protected $area;

    /**
     * Filters to apply
     *
     * @var array
     */
    protected $filters = [ ];

    /**
     * The added scripts
     *
     * @var array
     */
    protected $scripts = [ ];

    /**
     * The added styles
     *
     * @var array
     */
    protected $styles = [ ];

    /**
     * The dependencies this group has (other groups)=
     *
     * @var array
     */
    protected $dependencies = [ ];


    protected $sorter;

    /**
     * @var \Laradic\Assets\Factory
     */
    protected $factory;

    /**
     * @var \Assetic\Asset\AssetCollectionInterface
     */
    protected $collection;

    /**
     * @var \Laradic\Support\Filesystem
     */
    protected $files;

    /**
     * @var \Illuminate\Contracts\Cache\Store
     */
    protected $cache;

    /**
     * @var \Illuminate\Contracts\Container\Container
     */
    protected $container;

    /**
     * AssetGroup constructor.
     *
     * @param \Laradic\Contracts\Assets\Factory|\Laradic\Assets\Factory                $factory
     * @param \Laradic\Assets\Builder\Area                                             $area
     * @param \Illuminate\Contracts\Container\Container                                $container
     * @param \Illuminate\Contracts\Cache\Repository|\Illuminate\Contracts\Cache\Store $cache
     * @param \Laradic\Support\Filesystem                                              $files
     * @param                                                                          $id
     * @param array                                                                    $dependencies
     */
    public function __construct(
        #parents
        FactoryContract $factory,
        Area $area,

        #resolve
        Container $container,
        Repository $cache,
        Filesystem $files,

        #properties
        $id,
        $dependencies = [ ]
    )
    {
        $this->factory = $factory;
        $this->area    = $area;

        $this->container = $container;
        $this->cache     = $cache;
        $this->files     = $files;

        $this->id           = $id;
        $this->dependencies = $dependencies;
    }

    /**
     * Switch to another group
     *
     * @param       $id
     * @param array $dependencies
     * @param bool  $default
     *
     * @return \Laradic\Assets\Builder\Group
     */
    public function group($id, $dependencies = [ ], $default = false)
    {
        return $this->area->group($id, $dependencies, $default);
    }


    /**
     * Add a filter for this group
     *
     * @param $extension
     * @param $callback
     *
     * @return $this
     */
    public function addFilter($extension, $callback)
    {
        if ( is_string($callback) )
        {
            $callback = function () use ($callback)
            {


                return new $callback;
            };
        }
        elseif ( !$callback instanceof Closure )
        {
            throw new InvalidArgumentException('Callback is not a closure or reference string.');
        }
        $this->filters[ $extension ][] = $callback;

        return $this;
    }

    /**
     * Get filters by given extension
     *
     * @param $extension
     *
     * @return array
     */
    public function getFilters($extension)
    {
        $filters = [ ];
        if ( !array_key_exists($extension, $this->filters) )
        {
            return [ ];
        }
        foreach ( $this->filters[ $extension ] as $cb )
        {
            $filters[] = new $cb();
        }

        return $filters;
    }

    /**
     * Adds an asset to this group
     *
     * @param       $handle
     * @param null  $path
     * @param array $dependencies
     *
     * @return $this
     */
    public function add($handle, $path = null, $dependencies = [ ])
    {
        # 'cast' to array
        if ( is_string($dependencies) )
        {
            $dependencies = [ $dependencies ];
        }

        # resolve asset instance and handle
        if ( $handle instanceof AssetInterface )
        {
            $asset  = $handle;
            $handle = $asset->getHandle();
        }
        elseif ( !is_null($path) )
        {
            $asset = $this->factory->create($handle, $path, $dependencies);
        }
        else
        {
            throw new \InvalidArgumentException("Parameter path was null: $path");
        }
        $asset->setGroup($this);

        $type = $asset->getType();

        $asset->setDependencies($dependencies);

        $this->{"{$type}s"}[ $handle ] = [
            'handle'  => $handle,
            'asset'   => $asset,
            'type'    => $type,
            'depends' => $dependencies
        ];

        return $this;
    }

    /**
     * getAsset method
     *
     * @param $type
     * @param $handle
     *
     * @return AssetInterface
     */
    public function getAsset($type, $handle)
    {
        return $this->{"{$type}s"}[ $handle ][ 'asset' ];
    }

    /**
     * getAssets
     *
     * @param string $type 'scripts' or 'styles'
     *
     * @return mixed
     */
    public function getAssets($type)
    {
        return $this->{"{$type}"};
    }

    /**
     * getSorted
     *
     * @param string $type 'scripts' or 'styles'
     *
     * @param array  $only
     *
     * @return \Laradic\Assets\Assetic\Asset[]
     */
    public function getSortedAssets($type, array $only = null)
    {
        $sorter = new Sorter();
        foreach ( $this->{"{$type}"} as $handle => $assetData )
        {
            if ( $only !== null && in_array($handle, $only, true) )
            {
                continue;
            }
            $sorter->addItem($assetData[ 'asset' ]);
        }
        $assets = [ ];
        foreach ( $sorter->sort() as $handle )
        {
            $assets[] = $this->getAsset(Str::singular($type), $handle);
        }

        return $assets;
    }

    /**
     * Compile the given type in this group
     *
     * @param       $type
     * @param bool  $combine
     *
     * @param array $only
     *
     * @return \Laradic\Assets\Compiler\CompiledCollection
     */
    public function compile($type, $combine = true, array $only = null)
    {
        $compiler = $this->factory->getCompiler();
        $assets   = $this->getSortedAssets($type, $only);

        return $compiler->compileAssets($assets, $combine);
    }

    /**
     * Compile all styles in this group
     *
     * @param bool  $combine
     *
     * @param array $only
     *
     * @return \Laradic\Assets\Compiler\CompiledCollection
     */
    public function compileStyles($combine = true, array $only = null)
    {
        return $this->compile('styles', $combine, $only);
    }

    /**
     * Compile all scripts in th
     *
     * @param bool  $combine
     *
     * @param array $only
     *
     * @return \Laradic\Assets\Compiler\CompiledCollection
     */
    public function compileScripts($combine = true, array $only = null)
    {
        return $this->compile('styles', $combine, $only);
    }

    /**
     * Get the value of name
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    public function getCacheKey($type)
    {
        $key = md5($this->id . $type);
        foreach ( $this->filters as $filter )
        {
            $key .= $filter instanceof HashableInterface ? $filter->hash() : serialize($filter);
        }

        return md5($key);
    }

    /**
     * get dependencies value
     *
     * @return array
     */
    public function getDependencies()
    {
        return $this->dependencies;
    }

    /**
     * Set the dependencies value
     *
     * @param array $dependencies
     *
     * @return Group
     */
    public function setDependencies($dependencies)
    {
        $this->dependencies = $dependencies;

        return $this;
    }

    /**
     * get item key/identifier
     *
     * @return string|mixed
     */
    public function getHandle()
    {
        return $this->getId();
    }

    /**
     * @return \Laradic\Assets\Builder\Area
     */
    public function getArea()
    {
        return $this->area;
    }


    public function __toString()
    {
        return '';
    }
}
