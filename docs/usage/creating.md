<!---
title: Creating assets
subtitle: Single asset creation and low level usage.
-->

This will make a `Asset` instance based on the given params. See the `Asset` documentation for posibilities.
```php
$asset = Asset::create('script', 'global/plugins/jquery.min.js');
```

#### Methods
The `Asset` class extends Assetic's `FileAsset` class and adds a few methods. I should note that these methods aren't that important. 
Most of the time you would want to use asset grouping and group compilations.

| Method            | Return            | Description                                |
|:------------------|:-----------------:|:-------------------------------------------|
| `url()`           | string            | Get the full url to this asset             |
| `uri()`           | string            | Get the uri to this asset                  |
| `getType()`       | string            | Returns the type of this asset defined by `laradic.assets.types` config |
| `getExt()`        | string            | Get the file extension of this asset       |
| `getHandle()`     | string            | Get the dependency sorter handle (id/name) |
| `compile()`       | `Laradic\Assets\Compiler\Compiled` | Compile this asset |
| `getDependencies()`                               | array              | Gets dependencies            |
| `setDependencies(array $dependencies)`            | `$this`            | Sets dependencies            |
| `ensureFilter(FilterInterface $filter)`           | `$this`            | Ensures that the given filter is added             |
| `getFilters()`           | `array`            | Returns a collection of filters added to the asset |
| `clearFilters()`           | `void`            | Clears all filters present on the asset |
| `dump(FilterInterface $filter = null)`  | string             | Runs all the filters and returns the resulting string              |
| `getGroup()`                                      | `Group`            | Get the group of this asset             |
| `setGroup(Group $group)`                          | `$this`            | Set the group of this asset             |
| `inGroup()`                                       | boolean            | Returns true if the asset is in a  group             |
  
  
