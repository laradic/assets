Laradic Assets
====================

[![Build Status](https://img.shields.io/travis/laradic/assets.svg?&style=flat-square)](https://travis-ci.org/laradic/assets)
[![Scrutinizer coverage](https://img.shields.io/scrutinizer/coverage/g/laradic/assets.svg?&style=flat-square)](https://scrutinizer-ci.com/g/laradic/assets)
[![Scrutinizer quality](https://img.shields.io/scrutinizer/g/laradic/assets.svg?&style=flat-square)](https://scrutinizer-ci.com/g/laradic/assets)
[![Source](http://img.shields.io/badge/source-laradic/assets-blue.svg?style=flat-square)](https://github.com/laradic/assets)
[![License](http://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](https://tldrlegal.com/license/mit-license)

Laradic Assets is a package for the Laravel 5 framework.

The package follows the FIG standards PSR-1, PSR-2, and PSR-4 to ensure a high level of interoperability between shared PHP code.

Installation
------------

```bash
composer require laradic/assets "~1.0"
```

Alternatively, for some of the Assetic filters to work you might need one of the following:
```JSON
{
        "leafo/lessphp": "^0.3.7",
        "leafo/scssphp": "~0.1",
        "meenie/javascript-packer": "^1.1",
        "mrclay/minify": "<2.3",
        "natxet/cssmin": "3.0.4",
        "patchwork/jsqueeze": "~1.0|~2.0",
        "ptachoire/cssembed": "~1.0",
        "twig/twig": "~1.23|~2.0"
}
```


Documentation
-------------
For the **full documenation**, check out the [Laradic Assets](/docs/index.md) package documenatation.



Quick glance
------------

The `laradic/assets` package is a Laravel 5 asset manager. It uses `Assetic`'s filters in order to provide high functionality. 
It provides a convienient way to handle your assets. 


#### Overview
- A single `Asset` can be defined and compiled
- Compiling will run all configured filters on the asset and write the result to the `cache_path`. Returns an instance of `Compiler\Compiled`
- `Asset`s can be `Group`ed inside `Area`s. You could consider `Area` to be a 'group of groups'. You could for example create `package-name/frontend` and `package-name/admin`.  
- To use `Group`s, you will have to define at least 1 `Area`


#### Creation
This will make a `Asset` instance based on the given params. See the `Asset` documentation for posibilities.
```php
$asset = Asset::create('script', 'global/plugins/jquery.min.js');
```

#### Compilation
This will run all configured filters on the asset and write the result to the `cache_path`. Returns an instance of `Compiler\Compiled`
```php
$compiled = Asset::compile('script', 'global/plugins/jquery.min.js');
echo $compiled->getUrl();   # full url to the compiled asset
echo $compiled->getHtml();  # script/link html tag
echo $compiled->getUri();   # uri to the compiled asset
echo $compiled->getPath();  # absolute file path to the compiled asset
```

#### Grouping
Grouping assets brings a few advantages: 
- Allows dependency definitions for `Asset`s within its `Group`
- Allows dependency definitions for `Group`s within its `Area`
- Manually or automaticly compile and `Area`s, `Group`s and `Asset`s.
```php
$area = Asset::area('area/package');
$group = $area->group('global');
$group->add('bootstrap', 'css/bootstrap.css');
$group->add('font-awesome', 'global/plugins/font-awesome/css/font-awesome.min.css', 'bootstrap');
```

###### Chaining
```php
Asset::area('area/package')
     ->group('global')
     ->add('bootstrap', 'css/bootstrap.css')
     ->add('font-awesome', 'global/plugins/font-awesome/css/font-awesome.min.css', 'bootstrap');

// At a later point, you can continue adding
Asset::area('area/package')
     ->group('global')
     ->add('simple-line-icons', 'global/plugins/simple-line-icons/simple-line-icons.min.css', 'bootstrap')
     ->add('uniform', 'global/plugins/uniform/css/uniform.default.css', 'bootstrap');
```

###### Compilation and output
After being done with defining the area/group, you can manually trigger compilation and use the `Compiler\Compiled::getHtml` method to output it. 
```php
Asset::area('area/package')
     ->group('global')
     ->compile('script', $combine = true)
     ->getHtml();
```

The `Asset` facade links to the `Factory` class instance. The area, group and asset definition utilize the `NamespacedItemResolver`. 
This means some `Factory` methods accept such definitions:

```php
// Query
Asset::query
// Compiles the scripts in group
Asset::compile('script', 'area/package::global', $combine = true)->getHtml();
```

