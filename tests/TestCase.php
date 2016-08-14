<?php

namespace Laradic\Tests\Assets;

abstract class TestCase extends \Laradic\Testing\Native\AbstractTestCase
{
   /**
    * {@inheritdoc}
    */
    protected function getPackageRootPath()
    {
        return __DIR__ . DIRECTORY_SEPARATOR . '..';
    }
}
