<?php

namespace Cajudev\Rest\Annotations;

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
