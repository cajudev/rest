<?php

namespace Cajudev\Rest\Annotations;

use Doctrine\Common\Annotations\AnnotationReader;

final class AnnotationManager
{
    private $instance;
    private $reflection;
    private $reader;

    public function __construct(object $instance) {
        $this->instance = $instance;
        $this->reflection = new \ReflectionClass($instance);
        $this->reader = new AnnotationReader();
    }

    public function getAllPropertiesWith(string $annotationName) {
        $properties = [];

        foreach ($this->reflection->getProperties() as $property) {
            if ($annotation = $this->reader->getPropertyAnnotation($property, $annotationName)) {
                $properties[$property->getName()] = $annotation;
            }
        }

        return $properties;
    }
}
