<?php

namespace Cajudev\Rest\Annotations;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 */
final class Payload
{
    /**
     * @var array
     */
    public $context;

    /**
     * @var string
     */
    public $property;

    /**
     * @var array
     */
    public $properties;

     /**
     * @var string
     */
    public $format;
}
