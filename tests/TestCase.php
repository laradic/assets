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

namespace Laradic\Tests\Assets;

abstract class TestCase extends \Laradic\Testing\Native\AbstractTestCase
{
   /**
    * {@inheritdoc}
    */
    protected function getPackageRootPath()
    {
        return __DIR__ . DIRECTORY_SEPARATOR . '..';
        app()->make('laradic.assets')->create();

    }
}
