<?php

namespace Cajudev\Rest;

use Doctrine\Common\Collections\Collection;

class EntityList
{
    private $entities;

    /**
     * @param array|Collection $entities
     */
    public function __construct($entities = [])
    {
        $this->entities = $entities;
    }

    public function count(): int
    {
        if ($this->entities instanceof Collection) {
            return $this->entities->count();
        }
        return count($this->entities);
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
