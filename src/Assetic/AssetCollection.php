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
namespace Laradic\Assets\Assetic;

use Laradic\Assets\Builder\Group;
use Laradic\Assets\Compiler\CompiledCollection;
use Laradic\Assets\Contracts\Factory as FactoryContract;

/**
 * This is the class AssetCollection.
 *
 * @package        Laradic\Themes
 * @author         Laradic
 * @copyright      Copyright (c) 2015, Laradic. All rights reserved
 */
class AssetCollection extends \Assetic\Asset\AssetCollection implements AssetInterface
{
    /**
     * @var \Laradic\Contracts\Assets\Factory|\Laradic\Assets\Factory
     */
    protected $factory;

    /**
     * @var CompiledCollection
     */
    protected $compiled;

    /**
     * The Group instance
     *
     * @var Group
     */
    protected $group;

    /**
     * AssetCollection constructor.
     *
     * @param \Laradic\Contracts\Assets\Factory|\Laradic\Assets\Factory $factory
     * @param array                                                     $assets
     */
    public function __construct(FactoryContract $factory, array $assets)
    {
        $this->factory = $factory;

        parent::__construct($assets);
    }

    /**
     * compile method
     *
     * @return \Laradic\Assets\Compiler\CompiledAsset|\Laradic\Assets\Compiler\CompiledCollection
     */
    public function compile()
    {
        if (!isset($this->compiled)) {
            $this->compiled = $this->factory->getCompiler()->compile($this);
        }

        return $this->compiled;
    }

    /** Instantiates the class */
    public function getCacheKey()
    {
        $key = '';
        foreach ($this->all() as $asset) {
            if (!$asset instanceof Asset) {
                continue;
            }
            $key .= $asset->getCacheKey();
        }

        return 'col_' . $key;
    }

    /**
     * getHandle method
     *
     * @return string
     */
    public function getHandle()
    {
        return 'col_';
    }

    /**
     * getLastModifiedHash method
     *
     * @return string
     */
    public function getLastModifiedHash()
    {
        return md5($this->getLastModified());
    }

    /**
     * Get the value of ext
     *
     * @return mixed
     */
    public function getExt()
    {
        return head($this->all())->getExt();
        #return $this->getType() === 'style' ? 'css' : 'js';
    }

    /**
     * getType method
     *
     * @return int|string
     */
    public function getType()
    {
        return $this->factory->resolveType($this);
    }

    /**
     * @return null|Group
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Set the group value
     *
     * @param null $group
     *
     * @return AssetInterface
     */
    public function setGroup(Group $group)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * inGroup method
     *
     * @return bool
     */
    public function inGroup()
    {
        return isset($this->group);
    }
}
