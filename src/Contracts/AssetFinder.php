<?php
/**
 * Part of the Laradic PHP packages.
 *
 * License and copyright information bundled with this package in the LICENSE file
 */
namespace Laradic\Assets\Contracts;

/**
 * This is the class AssetFinderInterface.
 *
 * @package        Laradic\Assets
 * @author         Laradic
 * @copyright      Copyright (c) 2015, Laradic. All rights reserved
 */
interface AssetFinder
{
    public function getPath($key);
}
