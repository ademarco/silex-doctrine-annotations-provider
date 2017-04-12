
[![Build Status](https://travis-ci.org/skalpa/silex-doctrine-annotations-provider.svg?branch=master)](https://travis-ci.org/skalpa/silex-doctrine-annotations-provider)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/skalpa/silex-doctrine-annotations-provider/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/skalpa/silex-doctrine-annotations-provider/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/skalpa/silex-doctrine-annotations-provider/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/skalpa/silex-doctrine-annotations-provider/?branch=master)

# Doctrine Annotations Service Provider for Silex 2.x / Pimple 3.x

Lets you to use the Doctrine annotations reader in your
Silex/Pimple application.

## Installation

Install the service provider using composer:

```bash
composer require skalpa/silex-doctrine-annotations-provider
```

## Registration

```php
$app->register(new \Skalpa\Silex\Doctrine\AnnotationsServiceProvider());

$fooAnnotations = $app['annotations']->getClassAnnotations(new \ReflectionClass('Foobar\FooClass'));
```

## Configuration parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `annotations.use_simple_reader`   | `bool`                   | `false`         | Whether to use the Doctrine `AnnotationReader` or `SimpleAnnotationReader` class |
| `annotations.register_autoloader` | `bool`                   | `true`          | Whether to autoload annotations using the PHP autoloader |
| `annotations.cache`               | `string`&#124;`Cache`           | `ArrayCache`    | `Doctrine\Common\Cache\Cache` instance or name of a service that implements `Doctrine\Common\Cache\Cache` |
| `annotations.debug`               | `bool`                   | `$app['debug']` | Whether the cached reader should invalidate the cache files when the PHP class with annotations changed |
| `annotations.ignored_names`       | `string[]`               | `[]`            | List of names that should be ignored by the annotations reader (Note: this is not supported by the `SimpleAnnotationReader`) |

## Configuring the annotations cache

By default the cache used by the annotations reader is an `ArrayCache`,
which is probably not something you'll want to use on a production server.

If you already use a Doctrine Cache service provider and want to use an
already registered cache service, set `annotations.cache` to the name
of the service:

```php
// The DoctrineCacheServiceProvider will register a service named "cache"
$app->register(new DoctrineCacheServiceProvider());

// Cache annotations using the "cache" service
$app->register(new AnnotationsServiceProvider(), [
    'annotations.cache' => 'cache',
]);
```

Alternatively, you can override the `annotations.cache` service and
provide your own cache provider:

```php
$app->register(new AnnotationsServiceProvider(), [
    'annotations.cache' => function () {
        $cache = new \Doctrine\Common\Cache\PhpFileCache(__DIR__.'/cache);
        $cache->setNamespace('annotations');

        return $cache;
    },
]);
```
