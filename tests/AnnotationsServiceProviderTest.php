<?php

namespace Skalpa\Silex\Doctrine\Tests;

use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Annotations\SimpleAnnotationReader;
use Doctrine\Common\Cache\ArrayCache;
use PHPUnit\Framework\TestCase;
use Pimple\Container;
use Skalpa\Silex\Doctrine\AnnotationsServiceProvider;

class AnnotationsServiceProviderTest extends TestCase
{
    public function getContainer(array $parameters = [])
    {
        $container = new Container();
        $container->register(new AnnotationsServiceProvider(), $parameters);

        return $container;
    }

    public function testDefaultConfiguration()
    {
        $container = $this->getContainer();
        $annotation = $container['annotations']->getClassAnnotation(
            new \ReflectionClass(Fixtures\AnnotatedClass::class),
            Fixtures\TestAnnotation::class
        );

        $this->assertInstanceOf(CachedReader::class, $container['annotations']);
        $this->assertInstanceOf(Fixtures\TestAnnotation::class, $annotation);
    }

    public function testCacheIsService()
    {
        $cache = $this->getMockBuilder(ArrayCache::class)
            ->setMethods(['fetch'])
            ->getMock();

        $cache->expects($this->once())
            ->method('fetch')
            ->with(Fixtures\AnnotatedClass::class)
            ->willReturn(false);

        $container = $this->getContainer([
            'annotations.cache' => function () use ($cache) {
                return $cache;
            },
        ]);
        $container['annotations']->getClassAnnotations(
            new \ReflectionClass(Fixtures\AnnotatedClass::class)
        );
    }

    public function testCacheIsServiceName()
    {
        $cache = $this->getMockBuilder(ArrayCache::class)
            ->setMethods(['fetch'])
            ->getMock();

        $cache->expects($this->once())
            ->method('fetch')
            ->with(Fixtures\AnnotatedClass::class)
            ->willReturn(false);

        $container = $this->getContainer([
            'foo.cache' => function () use ($cache) {
                return $cache;
            },
            'annotations.cache' => 'foo.cache',
        ]);
        $container['annotations']->getClassAnnotations(
            new \ReflectionClass(Fixtures\AnnotatedClass::class)
        );
    }

    public function testCacheCanBeDisabled()
    {
        $container = $this->getContainer();
        unset($container['annotations.cache']);

        $this->assertInstanceOf(AnnotationReader::class, $container['annotations']);
    }

    public function testCanUseSimpleReader()
    {
        $container = $this->getContainer([
            'annotations.use_simple_reader' => true,
        ]);
        unset($container['annotations.cache']);

        $this->assertInstanceOf(SimpleAnnotationReader::class, $container['annotations']);
    }

    /**
     * @runInSeparateProcess
     */
    public function testCanDisableAutoloaderRegistration()
    {
        $this->expectException(AnnotationException::class);
        $this->expectExceptionMessage('was never imported');

        $container = $this->getContainer([
            'annotations.register_autoloader' => false,
        ]);

        $container['annotations']->getClassAnnotations(new \ReflectionClass(Fixtures\AnnotatedWithUnloadedClass::class));
    }

    /**
     * @runInSeparateProcess
     */
    public function testAddingIgnoredNames()
    {
        $container = $this->getContainer([
            'annotations.ignored_names' => ['FooAnnotation'],
        ]);

        $container['annotations']->getClassAnnotations(new \ReflectionClass(Fixtures\FooAnnotatedClass::class));
    }
}
