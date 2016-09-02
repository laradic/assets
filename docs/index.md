<!---
title: Overview
subtitle: A quick overview of some features
author: Laradic
-->


The `laradic/assets` package is a Laravel 5 asset manager. It uses `Assetic`'s filters in order to provide high functionality. 
It provides a convienient way to handle your assets. 

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

