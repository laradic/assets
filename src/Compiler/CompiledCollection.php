<?php

namespace Laradic\Assets\Compiler;

/**
 * This is the class CompiledCollection.
 *
 * @package        Laradic\Assets
 * @author         Laradic
 * @copyright      Copyright (c) 2015, Laradic. All rights reserved
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
     * make method
     *
     * @param array $items
     *
     * @return \Laradic\Assets\Compiler\CompiledCollection
     */
    public static function make(array $items = [ ])
    {
        return app()->make(static::class, compact('items'));
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
        return $this->items->transform(function (Compiled $item) use ($secure) {
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
        return $this->items->transform(function (Compiled $item) {
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
        return $this->items->transform(function (Compiled $item) {
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
        return $this->items->transform(function (Compiled $item) {
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
