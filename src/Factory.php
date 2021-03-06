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

namespace Laradic\Assets;

use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\NamespacedItemResolver;
use Illuminate\Support\Traits\Macroable;
use Laradic\Assets\Assetic\Asset;
use Laradic\Assets\Assetic\AssetCollection;
use Laradic\Assets\Assetic\AssetInterface;
use Laradic\Assets\Contracts\AssetFinder as AssetFinderContract;
use Laradic\Assets\Contracts\Factory as FactoryContract;
use Laradic\Support\Path;
use Laradic\Support\Str;

/**
 * This is the class AssetFactory.
 *
 * @package        Laradic\Assets
 * @author         Laradic
 * @copyright      Copyright (c) 2015, Laradic. All rights reserved
 */
class Factory implements FactoryContract
{
    use Macroable;

    //#region: Properties

    /**
     * Debug switch, if null then app.debug config will be used.
     *
     * @var null|bool
     */
    protected $debug = null;

    /**
     * Asset type map using file extensions
     *
     * @var array
     */
    protected $types = [];

    /**
     * Cache directory path, relative to public_path
     *
     * @var string
     */
    protected $cachePath;

    /**
     * The Area class instances with their id as key
     *
     * @var array
     */
    protected $areas = [];

    /**
     * Global filters that should be applied on all AssetInterfaces
     *
     * @var array
     */
    protected $globalFilters = [];

    /**
     * @var \Illuminate\Contracts\Container\Container
     */
    protected $container;

    /**
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * @var \Illuminate\Contracts\Routing\UrlGenerator
     */
    protected $url;

    /**
     * @var \Laradic\Assets\Contracts\AssetFinder
     */
    protected $finder;

    /**
     * @var \Illuminate\Support\NamespacedItemResolver
     */
    protected $resolver;

    /**
     * @var string
     */
    protected $assetClass = Asset::class;

    /**
     * @var string
     */
    protected $collectionClass = AssetCollection::class;

    /**
     * @var string
     */
    protected $areaClass = Builder\Area::class;

    /**
     * @var string
     */
    protected $compilerClass = Compiler\Compiler::class;

    //#endregion

    /**
     * Factory constructor.
     *
     * @param \Illuminate\Contracts\Container\Container  $container
     * @param \Illuminate\Filesystem\Filesystem          $files
     * @param \Illuminate\Contracts\Routing\UrlGenerator $url
     * @param \Laradic\Assets\Contracts\AssetFinder      $finder
     */
    public function __construct(Container $container, Filesystem $files, UrlGenerator $url, AssetFinderContract $finder)
    {
        $this->container = $container;
        $this->files     = $files;
        $this->url       = $url;
        $this->finder    = $finder;
        $this->resolver  = new NamespacedItemResolver;
    }

    /**
     * Creates an AssetInterface asset
     *
     * @param       $handle
     * @param       $path
     * @param array $dependencies
     *
     * @return Asset
     */
    public function create($handle, $path, array $dependencies = [])
    {
        $path = $this->getPath($path);
        /** @var \Laradic\Assets\Assetic\Asset $asset */
        $asset = new $this->assetClass($this, $handle, $path, $dependencies);
        foreach ($this->getGlobalFilters($asset->getExt()) as $filter) {
            $asset->ensureFilter($filter);
        }

        return $asset;
    }

    /**
     * createCollection method
     *
     * @param array $assets
     *
     * @return AssetInterface
     */
    public function createCollection(array $assets = [])
    {
        return new $this->collectionClass($this, $assets);
//        return $this->container->make('laradic.assets.collection', compact('assets'));
    }

    /**
     * Resolves the given query to a Area, Group or list of sorted Assets (eg: 'area/name' or 'area/name::group' or 'area/name::group.scripts')
     *
     * @param string $query - The query, wich is actually a NamespacedItemResolver key.
     *
     * @return \Laradic\Assets\Assetic\Asset[]|\Laradic\Assets\Builder\Area|\Laradic\Assets\Builder\Group
     */
    public function query($query)
    {
        list($area, $group, $type) = $this->resolver->parseKey($query);

        $area = $this->area($area);

        if ($group === null) {
            return $area;
        }

        $group = $area->group($group);

        if ($type === null) {
            return $group;
        }

        return $group->getSortedAssets($type);
    }

    /**
     * Compiles the result of the given query
     *
     * @param     string $type
     * @param     string $query - The query, wich is actually a NamespacedItemResolver key.
     * @param bool       $combine
     *
     * @return \Laradic\Assets\Compiler\CompiledCollection
     */
    public function compile($type, $query, $combine = true)
    {
        list($area, $group, $asset) = $this->resolver->parseKey($query);

        $area = $this->area($area);

        if ($group === null) {
            return $area->compile($type, $combine);
        }

        $group = $area->group($group);

        if ($asset === null) {
            return $group->compile($type, $combine);
        }

        return $group->getAsset($type, $asset)->compile();
    }

    /**
     * Get the Area that references $id
     *
     * @param string|mixed $id
     *
     * @return \Laradic\Assets\Builder\Area
     */
    public function area($id)
    {
        if (!array_key_exists($id, $this->areas)) {
            $this->areas[ $id ] = new $this->areaClass($this->container, $this, $id);
        }

        return $this->areas[ $id ];
    }

    /**
     * addGlobalFilter
     *
     * @param $extension
     * @param $callback
     *
     * @return \Laradic\Assets\Factory
     */
    public function addGlobalFilter($extension, $callback)
    {
        if (is_string($callback)) {
            $callback = function () use ($callback) {


                return new $callback;
            };
        } elseif (!$callback instanceof \Closure) {
            throw new \InvalidArgumentException('Callback is not a closure or reference string.');
        }
        $this->globalFilters[ $extension ][] = $callback;

        return $this;
    }

    /**
     * getGlobalFilters
     *
     * @param $extension
     *
     * @return array
     */
    public function getGlobalFilters($extension)
    {
        $filters = [];
        if (!array_key_exists($extension, $this->globalFilters)) {
            return [];
        }
        foreach ($this->globalFilters[ $extension ] as $cb) {
            $filters[] = $cb();
        }

        return $filters;
    }

    /**
     * Removes all chached files
     */
    public function deleteAllCached()
    {
        $this->files->delete($this->files->files($this->getCachePath()));
    }

    /**
     * Resolves the type of the given asset
     *
     * @param \Laradic\Assets\Assetic\AssetInterface $asset
     *
     * @return int|string
     */
    public function resolveType(AssetInterface $asset)
    {
        if ($asset instanceof AssetCollection) {
            $asset = head($asset->all());
        }
        $ext = pathinfo($asset->getSourcePath(), PATHINFO_EXTENSION);
        $ext = Str::removeLeft(strtolower($ext), '.');


        foreach ($this->types as $type => $types) {
            if (in_array($ext, $types, true)) {
                return $type;
            }
        }

        return 'other';
    }

    /**
     * Returns a <script src=""> html string
     *
     * @param       $key
     * @param array $attributes
     * @param bool  $secure
     *
     * @return string
     */
    public function script($key, array $attributes = [], $secure = false)
    {
        return Helper::script($this->getUrl($key), $attributes, $secure);
    }

    /**
     * Returns a <link href=""> html string
     *
     * @param       $key
     * @param array $attributes
     * @param bool  $secure
     *
     * @return string
     */
    public function style($key, array $attributes = [], $secure = false)
    {
        return Helper::style($this->getUrl($key), $attributes, $secure);
    }

    //#region: Asset finders

    /**
     * Returns the absolute path to the asset. Uses the AssetFinder to retreive the path with the given key
     *
     * @param $key
     *
     * @return mixed
     */
    public function getPath($key)
    {
        return $this->finder->getPath($key);
    }

    /**
     * Returns the url to the asset. Uses the AssetFinder to retreive the path with the given key
     *
     * @param $path
     *
     * @return string
     */
    public function getUrl($path)
    {
        return $this->url->asset($this->getUri($path));
    }

    /**
     * Returns the uri (relative path) to the asset. Uses the AssetFinder to retreive the path with the given key
     *
     * @param $key
     *
     * @return string
     */
    public function getUri($key)
    {
        return Path::makeRelative($this->getPath($key), public_path());
    }

    /**
     * Shorthand for getPath(). Returns the absolute path to the asset. Uses the AssetFinder to retreive the path with the given key
     *
     * @param $path
     *
     * @return mixed
     */
    public function path($path)
    {
        return $this->getPath($path);
    }

    /**
     * Shorthand for getUrl(). Returns the url to the asset. Uses the AssetFinder to retreive the path with the given key
     *
     * @param $path
     *
     * @return string
     */
    public function url($path)
    {
        return $this->getUrl($path);
    }

    /**
     * Shorthand for getUri(). Returns the uri (relative path) to the asset. Uses the AssetFinder to retreive the path with the given key
     *
     * @param $path
     *
     * @return string
     */
    public function uri($path)
    {
        return $this->getUri($path);
    }

    //#endregion

    //#region: Getters/Setters

    /**
     * Returns the cache directory path, relative to the public_path
     *
     * @return mixed
     */
    public function getCachePath()
    {
        return $this->cachePath;
    }

    /**
     * Set the cachePath value
     *
     * @param string $cachePath
     *
     * @return Factory
     */
    public function setCachePath($cachePath)
    {
        if (!$this->files->exists(public_path($cachePath))) {
            $this->files->makeDirectory(public_path($cachePath), 0755, true);
        }
        $this->cachePath = $cachePath;

        return $this;
    }

    /**
     * @param null $debug
     */
    public function setDebug($debug)
    {
        $this->debug = $debug;
    }

    /**
     * isDebug method
     *
     * @return bool|null
     */
    public function isDebug()
    {
        if (!is_null($this->debug)) {
            return $this->debug;
        }

        return config('app.debug', false);
    }

    /**
     * @return mixed
     */
    public function getTypes()
    {
        return $this->types;
    }

    /**
     * Set the types value
     *
     * @param mixed $types
     *
     * @return Factory
     */
    public function setTypes($types)
    {
        $this->types = $types;

        return $this;
    }

    /**
     * @return \Laradic\Assets\Compiler\Compiler
     */
    public function getCompiler()
    {
        $comp =  $this->container->make('laradic.assets.compiler');



        return $comp;
    }

    //#endregion
}
