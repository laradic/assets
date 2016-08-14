<?php
/**
 * Part of the Laradic PHP packages.
 *
 * License and copyright information bundled with this package in the LICENSE file
 */
namespace Laradic\Assets\Facades;

use Illuminate\Support\Facades\Facade;

class Asset extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'laradic.assets';
    }
}
