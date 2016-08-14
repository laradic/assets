<?php
/**
 * Part of the Laradic PHP packages.
 *
 * License and copyright information bundled with this package in the LICENSE file
 */


namespace Laradic\Assets\Assetic;

use Laradic\Assets\Builder\Group;

/**
 * Interface AssetInterface
 *
 * @package        Laradic\Assets
 * @author         Laradic
 * @copyright      Copyright (c) 2015, Laradic. All rights reserved
 */
interface AssetInterface extends \Assetic\Asset\AssetInterface
{
    /**
     * getExt method
     *
     * @return mixed
     */
    public function getExt();

    /**
     * getHandle method
     *
     * @return mixed
     */
    public function getHandle();

    /**
     * getLastModifiedHash method
     *
     * @return mixed
     */
    public function getLastModifiedHash();

    /**
     * getCacheKey method
     *
     * @return mixed
     */
    public function getCacheKey();

    /**
     * getType method
     *
     * @return mixed
     */
    public function getType();

    /**
     * compile method
     *
     * @return mixed
     */
    public function compile();

    /**
     * getGroup method
     *
     * @return mixed
     */
    public function getGroup();

    /**
     * setGroup method
     *
     * @param \Laradic\Assets\Builder\Group $group
     *
     * @return mixed
     */
    public function setGroup(Group $group);

    /**
     * inGroup method
     *
     * @return mixed
     */
    public function inGroup();
}
