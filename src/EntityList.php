<?php

namespace Cajudev\RestfulApi;

class EntityList
{
    private $entities;

    public function __construct(array $entities = [])
    {
        $this->entities = $entities;
    }

    /**
     * Executa o método chamado em cada entidade da lista
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
