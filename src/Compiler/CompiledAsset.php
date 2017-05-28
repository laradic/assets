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

/**
 * Created by IntelliJ IDEA.
 * User: radic
 * Date: 12/31/15
 * Time: 7:59 AM
 */

namespace Laradic\Assets\Compiler;

use Illuminate\Contracts\Routing\UrlGenerator;
use Laradic\Assets\Assetic\AssetInterface;
use Laradic\Assets\Helper;
use Laradic\Support\Path;

class CompiledAsset
{
    /**
     * The instance of the compiled asset
     *
     * @var \Laradic\Assets\Assetic\AssetInterface
     */
    protected $asset;

    /**
     * The path to the asset
     *
     * @var string
     */
    protected $path;

    /**
     * The UrlGenerator instance
     *
     * @var \Illuminate\Contracts\Routing\UrlGenerator
     */
    protected $url;

    /**
     * Compiled constructor.
     *
     * @param \Illuminate\Contracts\Routing\UrlGenerator $url
     * @param \Laradic\Assets\Assetic\AssetInterface     $asset
     * @param                                            $path8
     */
    public function __construct(UrlGenerator $url, AssetInterface $asset, $path)
    {
        $this->url   = $url;
        $this->asset = $asset;
        $this->path  = $path;
    }

    /**
     * make method
     *
     * @param \Laradic\Assets\Assetic\AssetInterface $asset
     * @param                                        $path
     *
     * @return \Laradic\Assets\Compiler\CompiledAsset
     */
    public static function make(AssetInterface $asset, $path)
    {
        return app()->makeWith(static::class, compact('asset', 'path'));
    }

    /**
     * getHtml method
     *
     * @param array $attributes
     * @param bool  $secure
     *
     * @return string
     */
    public function getHtml(array $attributes = [ ], $secure = null)
    {
        if ($this->asset->getType() === 'style') {
            return Helper::style($this->getUrl(), $attributes, $secure);
        }

        if ($this->asset->getType() === 'script') {
            return Helper::script($this->getUrl(), $attributes, $secure);
        }

        if ($this->asset->getType() === 'image') {
            return Helper::image($this->getUrl(), null, $attributes, $secure);
        }
    }

    /**
     * @return \Laradic\Assets\Assetic\Asset
     */
    public function getAsset()
    {
        return $this->asset;
    }

    /**
     * @return mixed
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Returns getUri
     *
     * @return string
     */
    public function getUri()
    {
        return Path::makeRelative($this->getPath(), public_path());
    }

    /**
     * @return \Illuminate\Contracts\Routing\UrlGenerator
     */
    public function getUrl($secure = null)
    {
        return $this->url->asset($this->getUri(), $secure);
    }
}
