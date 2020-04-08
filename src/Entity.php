<?php

namespace Cajudev\RestfulApi;

use Doctrine\Common\Collections\Collection;

abstract class Entity
{
    public function __construct(array $properties = [])
    {
        $this->populate($properties);
    }

    public function populate(array $properties = [])
    {
        foreach ($properties as $property => $value) {
            $this->$property = $value;
        }
    }

    public function __get($property)
    {
        $ref = new \ReflectionProperty($this, strtolower($property));
        $ref->setAccessible(true);
        $value = $ref->getValue($this);

        if ($value instanceof Collection) {
            return new CollectionProxy($this, $value);
        }

        return $value;
    }

    public function __set($property, $value)
    {
        $ref = new \ReflectionProperty($this, strtolower($property));
        $ref->setAccessible(true);
        $ref->setValue($this, $value);
    }
}
