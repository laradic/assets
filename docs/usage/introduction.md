<!---
title: Introduction
subtitle: A quick introduction and overview
-->

#### Preface
A common way to use `laradic/assets` would be to define `assets` in `groups` that are assigned to `areas`. 
However, there are many variants and alterations on how to implement assets. Trying to be as un-opinionated as possible.


#### Asset path resolving
You can configure the paths that have to be searched for assets you define in `laradic.assets.asset_paths`. 
The `AssetFinder` will use those paths to return the first found asset file path. If you use a namespaced path (`debugbar::path/to/file.js`),
it will look into the `public/vendor` and will try to locate the specified package.

The `AssetFinder` really is a simple implementation. This has been done on purpose as most applications
already have their own way of dealing with themes or asset paths. For integration with for example other `Theme` packages,
you would want to extend and override the `AssetFinder` and make it work with the custom logic your application is using.
You can find more about this in detail, with examples in [Extending the AssetFinder](/extending/asset-finder).


#### Basic usage of single Asset's
This will make a `Asset` instance based on the given params. See the `Asset` documentation for posibilities.
```php
$asset = Asset::create('script', 'path/to/jquery.min.js');
```

Create, compile and render the output tag (script/link) for a single asset.
```php
{!! Asset::create('script', 'path/to/jquery.js')->compile()->getHtml() !!}
```

Simply output the script/link tags without any compilation or caching.
```php
{!! Asset::script('path/to/jquery.js') !!}
{!! Asset::style('path/to/jquery.js') !!}
```

There's a lot more that will be covered in [Creating Assets](/usage/creating).


#### Defining Assets in Groups, with dependency sorting
To define your assets, you could make use of a `ServiceProvider`. 
Assets provides a convienient `ProvidesAssets` trait you can use in said provider.

If you work with a kind of 'themes' methodology, i'd suggest creating multiple providers, for each 'theme' its own.  

**AssetServiceProvider**
```php
class AssetServiceProvider extends ServiceProvider {
    use ProvidesAssets;
    
    public function register(){
        # Any code..
    }
    
    public function assets(){
        $this->area('admin/cms')
             ->group('global', [], true)
             ->add('jquery', 'path/to/jquery.js')
             ->add('bootstrap', 'path/to/bootstrap.js', ['jquery'])
             ->add('bootstrap', 'path/to/bootstrap.css')
             ->group('forms', ['global']) 
             ->add('jquery.form', 'path/to/jquery.form.js');            
    }
}
```
- The 'global' group does not depend on anything, hence the empty array
- The 'global' group is marked as enabled by default, hence the `true` parameter
- The 'forms' group depends on and requires the 'global' group  
- Since 'jquery' is in the 'global' group, it's not needed to define it as dependency here
- `Asset::area('admin/cms')->enableGroups('forms')` enables the forms group (or any others) to include in the final output compilation 


**layout.blade.php**
```html
<html>
    <head>
        <!--- Your code.. -->
        {!! Asset::area('admin/cms')->compile('styles')->getHtml() !!}
    </head>
    <body>
        <!--- Your code.. -->
        {!! Asset::area('admin/cms')->compile('scripts')->getHtml() !!}
    </body>
</html>
```


