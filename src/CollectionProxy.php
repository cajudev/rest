<?php

namespace Cajudev\RestfulApi;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

final class CollectionProxy
{
    private Entity $owner;
    private Collection $collection;

    public function __construct(Entity $owner, Collection $collection)
    {
        $this->collection = new ArrayCollection();
        $this->owner = $owner;
        $this->set($collection);
    }

    /**
     * Replace the current collection
     *
     * @param integer $index
     *
     * @return void
     */
    public function set(Collection $collection): void
    {
        $this->clear();
        foreach ($collection as $entity) {
            $this->add($entity);
        }
    }

    /**
     * Get one element from the collection
     *
     * @param integer $index
     *
     * @return void
     */
    public function get(int $index): Entity
    {
        return $this->collection->get($index);
    }

    /**
     * Add one element to the collection
     *
     * OneToMany have a inversed relation owner side, in this cases when adding a entity to a collection
     * we must inform to the added entity who is it owner.
     * The trick below allow us to have this behavior automatically.
     *
     * @param Entity $entity
     *
     * @return void
     */
    public function add(Entity $entity): void
    {
        $ref = new \ReflectionClass($this->owner);
        $owner = strtolower($ref->getShortName());

        if (property_exists($entity, $owner)) {
            $entity->$owner = $this->owner;
        }

        $this->collection->add($entity);
    }

    /**
     * Remove a element from the collection
     *
     * @param integer $index
     *
     * @return void
     */
    public function remove(int $index): void
    {
        $this->collection->remove($index);
    }

    /**
     * Remove all elements from the collection
     *
     * @param integer $index
     *
     * @return void
     */
    public function clear(): void
    {
        $this->collection->clear();
    }
}
