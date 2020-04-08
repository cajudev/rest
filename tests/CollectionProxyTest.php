<?php

use Cajudev\RestfulApi\Entity;

use PHPUnit\Framework\TestCase;
use Cajudev\RestfulApi\CollectionProxy;
use Doctrine\Common\Collections\ArrayCollection;

class Owner extends Entity
{
}

class Child extends Entity
{
    private $owner;
}

class CollectionProxyTest extends TestCase
{
    private $owner;
    private $collection;
    private $proxy;

    /** @before */
    public function init()
    {
        $this->owner = new Owner();
        $this->collection = new ArrayCollection();
        $this->proxy = new CollectionProxy($this->owner, $this->collection);
    }

    public function test_proxy_should_set_new_collection()
    {
        $this->proxy->set(new ArrayCollection());
        $this->proxy->add(new Child());
        $this->assertCount(0, $this->collection);
    }

    public function test_proxy_should_clear_elements_on_collection()
    {
        $child = new Child();
        $this->collection->add($child);
        $this->proxy->clear();
        $this->assertCount(0, $this->collection);
    }

    public function test_proxy_should_add_and_get_element_on_collection()
    {
        $child = new Child();
        $this->proxy->add($child);
        $this->assertEquals($child, $this->proxy->get(0));
    }

    public function test_proxy_should_automatically_set_the_owner_on_child_when_add()
    {
        $child = new Child();
        $this->proxy->add($child);
        $this->assertEquals($this->owner, $child->owner);
    }
}
