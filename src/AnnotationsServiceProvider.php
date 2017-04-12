<?php

namespace Skalpa\Silex\Doctrine;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Annotations\SimpleAnnotationReader;
use Doctrine\Common\Cache\ArrayCache;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * Doctrine annotations service provider for Silex/Pimple
 */
class AnnotationsServiceProvider implements ServiceProviderInterface
{
    private static $isConfigured = false;

    /**
     * Register the provided services.
     *
     * @param Container $container
     */
    public function register(Container $container)
    {
        $container['annotations.use_simple_reader']   = false;
        $container['annotations.register_autoloader'] = true;
        $container['annotations.debug']               = isset($container['debug']) ? $container['debug'] : false;
        $container['annotations.ignored_names']       = [];

        $container['annotations'] = function (Container $container) {
            $reader = $container['annotations.use_simple_reader'] ? $container['annotations.simple_reader'] : $container['annotations.reader'];

            if (isset($container['annotations.cache'])) {
                if (is_string($cache = $container['annotations.cache'])) {
                    $cache = $container[$cache];
                }

                return new CachedReader($reader, $cache, $container['annotations.debug']);
            }

            return $reader;
        };

        $container['annotations.cache'] = function () {
            return new ArrayCache();
        };

        $container['annotations.simple_reader'] = function (Container $container) {
            $this->configureDoctrine($container);

            return new SimpleAnnotationReader();
        };

        $container['annotations.reader'] = function (Container $container) {
            $this->configureDoctrine($container);

            return new AnnotationReader();
        };
    }

    private function configureDoctrine(Container $container)
    {
        if (!self::$isConfigured) {
            self::$isConfigured = true;
            if ($container['annotations.register_autoloader']) {
                AnnotationRegistry::registerLoader('class_exists');
            }
            foreach ($container['annotations.ignored_names'] as $name) {
                AnnotationReader::addGlobalIgnoredName($name);
            }
        }
    }
}
