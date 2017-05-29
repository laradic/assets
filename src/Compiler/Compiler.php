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

/**
 * Created by IntelliJ IDEA.
 * User: radic
 * Date: 12/31/15
 * Time: 5:52 AM
 */

namespace Laradic\Assets\Compiler;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Contracts\Routing\UrlGenerator;
use Laradic\Assets\Assetic\AssetInterface;
use Laradic\Assets\Contracts\Factory as FactoryContract;
use Laradic\Filesystem\Filesystem;
use Laradic\Support\Str;

/**
 * This is the class Compiler.
 *
 * @package        Laradic\Assets
 * @author         Laradic
 * @copyright      Copyright (c) 2015, Laradic. All rights reserved
 */
class Compiler
{
    /** @var bool */
    protected $cacheEnabled = true;

    /** @var */
    protected $assets;

    /** @var \Illuminate\Contracts\Cache\Repository */
    protected $cache;

    /** @var \Laradic\Filesystem\Filesystem */
    protected $fs;

    /** @var \Laradic\Assets\Contracts\Factory|\Laradic\Assets\Factory */
    protected $factory;

    /** @var \Illuminate\Contracts\Routing\UrlGenerator */
    protected $url;

    protected $compiledAssetClass = CompiledAsset::class;

    /**
     * Compiler constructor.
     *
     * @param \Illuminate\Contracts\Cache\Repository                                                           $cache
     * @param \Laradic\Filesystem\Filesystem|\Laradic\Support\Filesystem                                       $fs
     * @param \Laradic\Assets\Contracts\Factory|\Laradic\Assets\Factory|\Laradic\Assets\Contracts\AssetFactory $factory
     * @param \Illuminate\Contracts\Routing\UrlGenerator                                                       $url
     */
    public function __construct(Repository $cache, Filesystem $fs, FactoryContract $factory, UrlGenerator $url)
    {
        $this->cache   = $cache;
        $this->fs      = $fs;
        $this->factory = $factory;
        $this->url     = $url;
    }

    /**
     * collection method
     *
     * @param array $assets
     *
     * @return \Laradic\Assets\Assetic\AssetInterface
     */
    protected function collection($assets = [])
    {
        return $this->factory->createCollection($assets);
    }

    /**
     * debugging method
     *
     * @return bool|null
     */
    protected function debugging()
    {
        return $this->factory->isDebug();
    }

    /**
     * getPreparedPath method
     *
     * @param \Laradic\Assets\Assetic\AssetInterface $asset
     *
     * @return string
     */
    protected function getPreparedPath(AssetInterface $asset)
    {
        $cachePath = public_path($this->factory->getCachePath());

        if ($this->cacheEnabled && !$this->debugging()) {
            $lastModifiedHash = md5($asset->getLastModified());
            $filename         = $asset->getHandle() . '.' . $lastModifiedHash . '.' . $asset->getExt();
            $path             = $cachePath . '/' . $filename;


            if ($this->cache->has($asset->getCacheKey()) && $this->cache->get($asset->getCacheKey()) !== $asset->getLastModifiedHash()) {
                $this->fs->delete($path);
            }

            $this->cache->forever($asset->getCacheKey(), $asset->getLastModifiedHash());
        } //        elseif ($this->debugging()){
//            $path = Path::join($cachePath, $asset->getSourcePath());
//        }
        elseif ($asset->inGroup()) {
            $group = $asset->getGroup()->getId();
            $area  = $asset->getGroup()->getArea()->getId();
            $name  = $asset->getHandle();
            $ext   = $asset->getExt();
            $path  = Str::replace("{$area}_{$group}_{$name}.{$ext}", DIRECTORY_SEPARATOR, '-');
            $path  = path_join($cachePath, $path);
        } else {
            $path = path_join($cachePath, $asset->getSourcePath());
        }

        return $path;
    }

    /**
     * applyGlobalFilters method
     *
     * @param \Laradic\Assets\Assetic\AssetInterface $asset
     */
    protected function applyGlobalFilters(AssetInterface $asset)
    {
        foreach ($this->factory->getGlobalFilters($asset->getExt()) as $filter) {
            $asset->ensureFilter($filter);
        }
    }

    /**
     * compile method
     *
     * @param \Laradic\Assets\Assetic\AssetInterface $asset
     * @param bool                                   $force
     *
     * @return \Laradic\Assets\Compiler\CompiledAsset
     */
    public function compile(AssetInterface $asset, $force = false)
    {
        if (!$this->debugging()) {
            $this->applyGlobalFilters($asset);
        }

        $path = $this->getPreparedPath($asset);

        if (!$this->fs->exists($path) || $force === true) {
            $this->fs->put($path, $asset->dump());
        }

        if ($this->debugging()) {
            $path = $asset->getSourceDirectory() . DIRECTORY_SEPARATOR . $asset->getSourcePath();
        }
        return new $this->compiledAssetClass($this->url, $asset, $path); //CompiledAsset::make($asset, $path);
    }

    /**
     * compileAssets method
     *
     * @param array $assets
     * @param bool  $combine
     *
     * @return \Laradic\Assets\Compiler\CompiledCollection
     */
    public function compileAssets(array $assets = [], $combine = true)
    {
        if (!$this->debugging() && $combine === true) {
            $assets = [ $this->collection($assets) ];
        }

        $compiled = new CompiledCollection;


        foreach ($assets as $asset) {
            $compiled->push($this->compile($asset));
        }

        return $compiled;
    }
}
