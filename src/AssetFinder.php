<?php
/**
 * Part of the Laradic PHP packages.
 *
 * License and copyright information bundled with this package in the LICENSE file
 */
namespace Laradic\Assets;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\NamespacedItemResolver;
use Laradic\Assets\Exceptions\AssetNotFoundException;
use Laradic\Assets\Contracts\AssetFinder as AssetFinderContract;
use Laradic\Support\Path;

/**
 * This is the class AssetFileFinder.
 *
 * @package        Laradic\Assets
 * @author         Laradic
 * @copyright      Copyright (c) 2015, Laradic. All rights reserved
 */
class AssetFinder implements AssetFinderContract
{
    /**
     * A collection of paths to search for the given asset
     *
     * @var array
     */
    protected $assetPaths = [ ];

    /**
     * The
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /** Instantiates the class
     *
     * @param \Illuminate\Filesystem\Filesystem $files
     */
    public function __construct(Filesystem $files)
    {
        $this->assetPaths = config('laradic.assets.asset_paths');
        $this->files      = $files;

    }



    /**
     * getPath method
     *
     * @param $key
     *
     * @return string
     * @throws \Laradic\Assets\Exceptions\AssetNotFoundException
     */
    public function getPath($key)
    {
        list($section, $relativePath, $extension) = with(new NamespacedItemResolver)->parseKey($key);

        if ($section === null) {
            foreach ($this->assetPaths as $assetPath) {
                $path = Path::join(public_path($assetPath), $relativePath . '.' . $extension);
                if ($this->files->exists($path)) {
                    return $path;
                }
            }
        } else {
            $path = Path::join(public_path('vendor'), $section, $relativePath . '.' . $extension);
            if ($this->files->exists($path)) {
                return $path;
            }
        }

        throw new AssetNotFoundException("Could not find asset [{$key}]");
    }

    /**
     * get assetPaths value
     *
     * @return array
     */
    public function getAssetPaths()
    {
        return $this->assetPaths;
    }

    /**
     * Set the assetPaths value
     *
     * @param array $assetPaths
     * @return AssetFinder
     */
    public function setAssetPaths($assetPaths)
    {
        $this->assetPaths = $assetPaths;

        return $this;
    }
}
