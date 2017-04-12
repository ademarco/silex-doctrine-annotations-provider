<?php

namespace Skalpa\Silex\Doctrine\Tests\Fixtures;

/**
 * Test annotation.
 *
 * @Annotation
 * @Target({"CLASS", "METHOD"})
 */
final class TestAnnotation
{
    public $value = '';
}
