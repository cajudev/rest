<?php

namespace Cajudev\Rest\Annotation;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 */
final class Payload
{
    /**
     * @var string
     */
    public $property;

    /**
     * @var array
     */
    public $properties;
}
