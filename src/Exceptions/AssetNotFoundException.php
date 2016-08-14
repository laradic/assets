<?php
/**
 * Part of the Laradic PHP packages.
 *
 * License and copyright information bundled with this package in the LICENSE file
 */
namespace Laradic\Assets\Exceptions;

use Exception;

/**
 * This is the class AssetNotFoundException.
 *
 * @package        Laradic\Assets
 * @author         Laradic
 * @copyright      Copyright (c) 2015, Laradic. All rights reserved
 */
class AssetNotFoundException extends \Exception
{
    public function __construct($key)
    {
        $message = "Could not find asset [{$key}]";
        parent::__construct($message);
    }
}
