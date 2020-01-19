<?php

namespace Cajudev\RestfulApi;

abstract class Entity
{
    public function __construct(array $properties = [])
    {
        $this->setParams($properties);
    }

    public function setParams(array $properties = [])
    {
        foreach ($properties as $property => $value) {
            $this->set($property, $value);
        }
    }
    
    public function set(string $property, $value)
    {
        if ($value !== null) {
            $method = 'set' . ucfirst($property);
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }
    }
}
