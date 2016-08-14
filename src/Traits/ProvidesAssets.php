<?php
/**
 * Part of the Laradic PHP packages.
 *
 * License and copyright information bundled with this package in the LICENSE file
 */


namespace Laradic\Assets\Traits;

/**
 * This is the class ProvidesThemeAssets.
 *
 * @package        Laradic\Themes
 * @author         Laradic
 * @copyright      Copyright (c) 2015, Laradic. All rights reserved
 * @mixin \Laradic\Support\ServiceProvider
 */
trait ProvidesAssets
{
    /**
     * @var \Laradic\Assets\Factory
     */
    protected $assets;

    public function enableAssets()
    {
        if ( config('laradic.themes.assets.use') !== 'laradic' ) {
            return;
        }
        $registered = false;
        $this->app->make('events')->listen('composing:*', function () use (&$registered) {
            if ($registered === false) {
                $this->app->register(\Laradic\Assets\AssetsServiceProvider::class);
                $this->assets();
                $registered = true;
            }
        });
    }

    abstract protected function assets();

    protected function area($name)
    {
        return $this->app->make('laradic.assets')->area($name);
    }
}
