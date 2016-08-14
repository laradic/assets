<?php
/**
 * Created by IntelliJ IDEA.
 * User: radic
 * Date: 12/31/15
 * Time: 5:52 AM
 */

namespace Laradic\Assets\Compiler;

use Illuminate\Contracts\Cache\Repository;
use Laradic\Assets\Assetic\AssetInterface;
use Laradic\Contracts\Assets\Factory as FactoryContract;
use Laradic\Filesystem\Filesystem;
use Laradic\Support\Path;
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

    /** @var \Laradic\Support\Filesystem */
    protected $fs;

    /** @var \Laradic\Contracts\Assets\AssetFactory|\Laradic\Contracts\Assets\Factory|\Laradic\Assets\Factory */
    protected $factory;

    /**
     * Compiler constructor.
     *
     * @param \Illuminate\Contracts\Cache\Repository                         $cache
     * @param \Laradic\Support\Filesystem                                    $fs
     * @param \Laradic\Contracts\Assets\AssetFactory|\Laradic\Assets\Factory $factory
     */
    public function __construct(Repository $cache, Filesystem $fs, FactoryContract $factory)
    {
        $this->cache   = $cache;
        $this->fs      = $fs;
        $this->factory = $factory;
    }

    /**
     * collection method
     *
     * @param array $assets
     *
     * @return \Laradic\Assets\Assetic\AssetInterface
     */
    protected function collection($assets = [ ])
    {
        return $this->factory->createCollection($assets);
    }

    /**
     * make method
     *
     * @param array $assets
     *
     * @return \Laradic\Assets\Compiler\Compiler
     */
    public static function make($assets = [ ])
    {
        return app()->make(static::class);
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
            $path  = Path::join($cachePath, $path);
        } else {
            $path = Path::join($cachePath, $asset->getSourcePath());
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
     * @return \Laradic\Assets\Compiler\Compiled
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
        return Compiled::make($asset, $path);
    }

    /**
     * compileAssets method
     *
     * @param array $assets
     * @param bool  $combine
     *
     * @return \Laradic\Assets\Compiler\CompiledCollection
     */
    public function compileAssets(array $assets = [ ], $combine = true)
    {
        if (!$this->debugging() && $combine === true) {
            $assets = [ $this->collection($assets) ];
        }

        $compiled = CompiledCollection::make();

        foreach ($assets as $asset) {
            $compiled->push($this->compile($asset));
        }

        return $compiled;
    }
}
