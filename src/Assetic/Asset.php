<?php
/**
 * Part of the Laradic PHP packages.
 *
 * License and copyright information bundled with this package in the LICENSE file
 */
namespace Laradic\Assets\Assetic;

use Assetic\Asset\FileAsset;
use Assetic\Filter\FilterInterface;
use Assetic\Filter\HashableInterface;
use Laradic\Assets\Builder\Group;
use Laradic\Assets\Compiler\CompiledCollection;
use Laradic\Contracts\Assets\Factory as FactoryContract;
use Laradic\Contracts\Dependencies\Dependable;

/**
 * This is the class Asset.
 *
 * @package        Laradic\Themes
 * @author         Laradic
 * @copyright      Copyright (c) 2015, Laradic. All rights reserved
 */
class Asset extends FileAsset implements Dependable, AssetInterface
{
    /**
     * @var string
     */
    protected $handle;

    /**
     * @var \Laradic\Assets\Factory
     */
    protected $factory;

    /**
     * @var array
     */
    protected $dependencies;

    protected $group = null;

    /**
     * Asset constructor.
     *
     * @param \Laradic\Assets\Factory $factory
     * @param array                   $handle
     * @param null|string             $path
     * @param array                   $dependencies
     */
    public function __construct(FactoryContract $factory, $handle, $path, array $dependencies = [ ])
    {
        parent::__construct($path);
        $this->handle       = $handle;
        $this->dependencies = $dependencies;
        $this->factory      = $factory;
    }

    /**
     * Get the full url to this asset
     *
     * @return string
     */
    public function url()
    {
        return $this->factory->getUrl($this->handle);
    }

    /**
     * Get the uri to this asset
     *
     * @return string
     */
    public function uri()
    {
        return $this->factory->getUri($this->handle);
    }

    /**
     * Returns the type of this asset, uses the laradic.assets.types configuration definitions
     *
     * @return string
     */
    public function getType()
    {
        return $this->factory->resolveType($this);
    }

    /**
     * Get the value of ext
     *
     * @return mixed
     */
    public function getExt()
    {
        $type = $this->getType();
        if ($type === 'style') {
            return 'css';
        } elseif ($type === 'script') {
            return 'js';
        } elseif ($type === 'image') {
            return pathinfo($this->getSourcePath(), PATHINFO_EXTENSION);
        }
    }

    /**
     * @var CompiledCollection
     */
    protected $compiled;

    /**
     * Compile this asset
     *
     * @return \Laradic\Assets\Compiler\Compiled|\Laradic\Assets\Compiler\CompiledCollection
     */
    public function compile()
    {
        if (!isset($this->compiled)) {
            $this->compiled = $this->factory->getCompiler()->compile($this);
        }

        return $this->compiled;
    }

    /**
     * get dependencies
     *
     * @return array
     */
    public function getDependencies()
    {
        return $this->dependencies;
    }

    /**
     * setDependencies
     *
     * @param array $dependencies
     */
    public function setDependencies(array $dependencies)
    {
        $this->dependencies = $dependencies;
    }

    /**
     * get item key/identifier
     *
     * @return string|mixed
     */
    public function getHandle()
    {
        return $this->handle;
    }

    /**
     * getCacheKey
     *
     * @return string
     */
    public function getCacheKey()
    {
        $key = $this->handle . $this->getSourcePath();
        foreach ($this->getFilters() as $filter) {
            $key .= $filter instanceof HashableInterface ? $filter->hash() : serialize($filter);
        }

        return $key;
    }

    /**
     * load method
     *
     * @param \Assetic\Filter\FilterInterface|null $additionalFilter
     *
     * @return $this
     */
    public function load(FilterInterface $additionalFilter = null)
    {
        parent::load($additionalFilter);

        return $this;
    }

    /**
     * ensureFilter method
     *
     * @param \Assetic\Filter\FilterInterface $filter
     *
     * @return $this
     */
    public function ensureFilter(FilterInterface $filter)
    {
        parent::ensureFilter($filter);

        return $this;
    }

    public function getLastModifiedHash()
    {
        return md5($this->getLastModified());
    }

    /**
     * @return null
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
     * @return Asset
     */
    public function setGroup(Group $group)
    {
        $this->group = $group;

        return $this;
    }

    public function inGroup()
    {
        return isset($this->group);
    }
}
