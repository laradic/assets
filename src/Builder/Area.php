<?php
/**
 * Created by IntelliJ IDEA.
 * User: radic
 * Date: 12/31/15
 * Time: 3:10 AM
 */

namespace Laradic\Assets\Builder;

use Illuminate\Contracts\Container\Container;
use Laradic\Assets\Compiler\CompiledCollection;
use Laradic\Contracts\Assets\Factory;
use Laradic\Dependencies\Sorter;


class Area implements BuilderInterface
{
    /**
     * The unique id
     *
     * @var string
     */
    protected $id;

    /**
     * A collection of groups for this area
     *
     * @var array
     */
    protected $groups = [ ];

    /**
     * Contains a list of group id's that will be compiled if the compile method is called without defining any groups.
     *
     * @var array
     */
    protected $defaultGroups = [ ];

    /**
     * @var \Laradic\Contracts\Assets\Factory|\Laradic\Assets\Factory
     */
    protected $factory;

    /**
     * @var \Illuminate\Contracts\Container\Container
     */
    protected $container;

    /**
     * GroupContainer constructor.
     *
     * @param \Laradic\Contracts\Assets\Factory|\Laradic\Assets\Factory      $factory
     * @param \Illuminate\Contracts\Container\Container                      $container
     * @param                                                                $id
     */
    public function __construct(Container $container, Factory $factory, $id)
    {
        $this->factory   = $factory;
        $this->container = $container;
        $this->id        = $id;
    }

    /**
     * Returns the ID for this area
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Defines / Gets a group
     *
     * @param string|mixed      $id
     * @param array $dependencies
     * @param bool  $default - If true, the group will be added to the defaultGroups property, see docs there for more info
     *
     * @return \Laradic\Assets\Builder\Group
     */
    public function group($id, $dependencies = [ ], $default = false)
    {
        if (!array_key_exists($id, $this->groups)) {
            $this->groups[ $id ] = $this->container->make('laradic.assets.builder.group', [
                'area' => $this,
                'id'   => $id
            ]);
        }

        /** @var Group $group */
        $group = $this->groups[ $id ];

        $group->setDependencies(array_replace_recursive($group->getDependencies(), $dependencies));

        if ($default === true) {
            $this->defaultGroups[] = $id;
        }

        return $group;
    }

    /**
     * Switch to another area to define or compile assets there. A conveinence method while chaining
     *
     * @param $id
     *
     * @return \Laradic\Assets\Builder\Area
     */
    public function area($id)
    {
        return $this->factory->area($id);
    }

    /**
     * Compile the given type
     *
     * @param            $type
     * @param bool       $combine
     * @param null|array $groups
     *
     * @return CompiledCollection
     */
    public function compile($type, $combine = true, array $groups = null)
    {
        $compiler     = $this->factory->getCompiler();
        $sortedGroups = $this->getSortedGroups($groups);
        $assets       = [ ];
        foreach ($sortedGroups as $group) {
        /** @var Group $group */
            $assets = array_merge($assets, $group->getSortedAssets($type));
        }

        return $compiler->compileAssets($assets, $combine);
    }

    /**
     * compileStyles method
     *
     * @param bool $combine
     * @param null $groups
     *
     * @return CompiledCollection
     */
    public function compileStyles($combine = true, array $groups = null)
    {
        return $this->compile('styles', $combine, $groups);
    }

    /**
     * compileScripts method
     *
     * @param bool $combine
     * @param null $groups
     *
     * @return CompiledCollection
     */
    public function compileScripts($combine = true, array $groups = null)
    {
        return $this->compile('scripts', $combine, $groups);
    }

    /**
     * @param null|array $groups
     *
     * @return array
     */
    public function getSortedGroups(array $groups = null)
    {
        $groups = array_only($this->groups, $groups === null ? $this->defaultGroups : $groups);

        $sorter = new Sorter();
        foreach ($groups as $handle => $group) {
            $sorter->addItem($group);
        }

        $sortedGroups = [ ];
        foreach ($sorter->sort() as $handle) {
            $sortedGroups[] = $groups[ $handle ];
        }

        return $sortedGroups;
    }
}
