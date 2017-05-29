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

namespace Laradic\Assets\Compiler;

/**
 * This is the class CompiledCollection.
 *
 * @package        Laradic\Assets
 * @author         Laradic
 * @copyright      Copyright (c) 2015, Laradic. All rights reserved
 * @mixin \Illuminate\Support\Collection
 */
class CompiledCollection
{
    /** @var \Illuminate\Support\Collection */
    protected $items;

    /**
     * CompiledCollection constructor.
     *
     * @param array $items
     */
    public function __construct(array $items = [ ])
    {
        $this->items = collect($items);
    }

    /**
     * getHtml method
     *
     * @param bool $secure
     *
     * @return string
     */
    public function getHtml($secure = false)
    {
        return $this->items->transform(function (CompiledAsset $item) use ($secure) {
            $html = $item->getHtml([], $secure);
            return $html;
        })->implode('');
    }

    /**
     * getUrls method
     *
     * @return array
     */
    public function getUrls()
    {
        return $this->items->transform(function (CompiledAsset $item) {
            return $item->getUrl();
        })->toArray();
    }

    /**
     * getUris method
     *
     * @return array
     */
    public function getUris()
    {
        return $this->items->transform(function (CompiledAsset $item) {
            return $item->getUri();
        })->toArray();
    }

    /**
     * getPaths method
     *
     * @return array
     */
    public function getPaths()
    {
        return $this->items->transform(function (CompiledAsset $item) {
            return $item->getPath();
        })->toArray();
    }

    /**
     * __call method
     *
     * @param $method
     * @param $params
     *
     * @return mixed
     */
    public function __call($method, $params)
    {
        if (method_exists($this->items, $method)) {
            return call_user_func_array([ $this->items, $method ], $params);
        }
    }
}
