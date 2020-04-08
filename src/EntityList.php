<?php

namespace Cajudev\RestfulApi;

class EntityList
{
    private atrray $entities;

    public function __construct(array $entities = [])
    {
        $this->entities = $entities;
    }

    /**
     * Delegate the called method to all entities
     *
     * @param string $method
     * @param array $args
     * 
     * @return void
     */
    public function __call(string $method, array $args = [])
    {
        $ret = [];
        foreach ($this->entities as $entity) {
            if (method_exists($entity, $method)) {
                $ret[] = $entity->$method(...$args);
            }
        }
        return $ret;
    }
}
