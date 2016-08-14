<?php
/**
 * Part of the Laradic PHP packages.
 *
 * License and copyright information bundled with this package in the LICENSE file
 */


namespace Laradic\Assets\Builder;

interface BuilderInterface
{
    public function getId();

    public function compile($type, $combine = true, array $only = null);

    public function compileStyles($combine = true, array $only = null);

    public function compileScripts($combine = true, array $only = null);
}
