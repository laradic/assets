<?php
/**
 * Part of the Laradic PHP Packages.
 *
 * Copyright (c) 2017. Robin Radic.
 *
 * The license can be found in the package and online at https://laradic.mit-license.org.
 *
 * @copyright Copyright 2017 (c) Robin Radic
 * @license   https://laradic.mit-license.org The MIT License
 */

namespace Laradic\Assets\Builder;

use Assetic\Filter\HashableInterface;
use Closure;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Contracts\Container\Container;
use InvalidArgumentException;
use Laradic\Assets\Assetic\AssetInterface;
use Laradic\Assets\Contracts\Factory as FactoryContract;
use Laradic\DependencySorter\Dependable;
use Laradic\DependencySorter\Sorter;
use Laradic\Filesystem\Filesystem;
use Laradic\Support\Contracts\Stringable;
use Laradic\Support\Str;

/**
 * This is the class AssetGroup.
 *
 * @package        Laradic\Themes
 * @author         Laradic
 * @copyright      Copyright (c) 2015, Laradic. All rights reserved
 */
class Group implements Dependable, BuilderInterface, Stringable
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
    protected $filters = [];

    /**
     * The added scripts
     *
     * @var array
     */
    protected $scripts = [];

    /**
     * The added styles
     *
     * @var array
     */
    protected $styles = [];

    /**
     * The dependencies this group has (other groups)=
     *
     * @var array
     */
    protected $dependencies = [];

    /** @var */
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
    public function __construct(FactoryContract $factory, Container $container, Repository $cache, Filesystem $files)
    {
        $this->factory = $factory;

        $this->container = $container;
        $this->cache     = $cache;
        $this->files     = $files;
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
    public function group($id, $dependencies = [], $default = false)
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
        if (is_string($callback)) {
            $callback = function () use ($callback) {


                return new $callback;
            };
        } elseif (!$callback instanceof Closure) {
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
        $filters = [];
        if (!array_key_exists($extension, $this->filters)) {
            return [];
        }
        foreach ($this->filters[ $extension ] as $cb) {
            $filters[] = new $cb();
        }

        return $filters;
    }

    /**
     * Adds an asset to this group
     *
     * @param       $handle
     * @param null  $path
     * @param array $depends
     *
     * @return $this
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function add($handle, $path = null, $depends = [])
    {
        # 'cast' to array
        if (is_string($depends)) {
            $depends = [ $depends ];
        }

        # resolve asset instance and handle
        if ($handle instanceof AssetInterface) {
            $asset  = $handle;
            $handle = $asset->getHandle();
        } elseif (!is_null($path)) {
            $asset = $this->factory->create($handle, $path, $depends);
        } else {
            throw new \InvalidArgumentException("Parameter path was null: $path");
        }
        $asset->setGroup($this);

        $type = $asset->getType();

        if ($this->has($type, $handle)) {
            throw new \LogicException("Could not add asset. Asset [{$handle}] already exists in group [{$this->id}].");
        }

        if (false === $this->hasAllHandles($depends)) {
            throw new \InvalidArgumentException("One or more of the given dependencies for Asset [{$this->area}::{$this}.{$handle}] does not exist in this group");
        }

        $asset->setDependencies($depends);

        $this->{"{$type}s"}[ $handle ] = compact('handle', 'asset', 'type', 'depends');

        return $this;
    }

    /**
     * Checks if all handles for the given type exist in the group
     *
     * @param       $type
     * @param array $handles
     *
     * @return bool
     */
    protected function hasAllHandles($type, $handles = [])
    {
        foreach ($handles as $dep) {
            if (false === $this->has($type, $dep)) {
                return false;
            }
        }
        return true;
    }

    /**
     * replace method
     *
     * @param       $handle
     * @param null  $path
     * @param array $depends
     *
     * @return $this
     */
    public function replace($handle, $path = null, $depends = [])
    {
        $type = $this->factory->resolveType($path);

        if ($handle instanceof AssetInterface) {
            $handle = $handle->getHandle();
        }

        if (false === $this->has($type, $handle)) {
            throw new \LogicException("Could not replace asset. Asset [{$handle}] does not exist in group [{$this->id}].");
        }

        $this->remove($type, $handle);
        $this->add($handle, $path, $depends);

        return $this;
    }

    /**
     * remove method
     *
     * @param $type
     * @param $handle
     *
     * @return $this
     */
    public function remove($type, $handle)
    {
        if (false === $this->has($type, $handle)) {
            throw new \LogicException("Could not remove asset. Asset [{$handle}] does not exist in group [{$this->id}].");
        }
        unset($this->{"{$type}s"}[ $handle ]);
        return $this;
    }

    /**
     * has method
     *
     * @param $type
     * @param $handle
     *
     * @return bool
     */
    public function has($type, $handle)
    {
        return array_key_exists($handle, $this->{"{$type}s"});
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
        foreach ($this->{"{$type}"} as $handle => $assetData) {
            if ($only !== null && in_array($handle, $only, true)) {
                continue;
            }
            $sorter->addItem($assetData[ 'asset' ]);
        }
        $assets = [];
        foreach ($sorter->sort() as $handle) {
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

    /**
     * getCacheKey method
     *
     * @param $type
     *
     * @return string
     */
    public function getCacheKey($type)
    {
        $key = md5($this->id . $type);
        foreach ($this->filters as $filter) {
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


    /**
     * __toString method
     *
     * @return string
     */
    public function __toString()
    {
        return (string)$this->getHandle();
    }
}
