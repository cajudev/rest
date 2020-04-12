<?php

use PHPUnit\Framework\TestCase;

use Cajudev\Rest\Entity;
use Cajudev\Rest\Collections\CollectionProxy;

use Doctrine\Common\Collections\ArrayCollection;

class EntityTest extends TestCase
{
    public function test_should_access_private_property_via_magic_method()
    {
        $entity = new class extends Entity {
            private int $number = 0;
        };
        $this->assertEquals(0, $entity->number);
    }

    public function test_should_change_private_property_via_magic_method()
    {
        $entity = new class extends Entity {
            private int $number = 0;
        };
        $entity->number = 999;
        $this->assertEquals(999, $entity->number);
    }

    public function test_should_return_proxy_when_property_is_collection()
    {
        $entity = new class extends Entity {
            private ArrayCollection $collection;

            public function __construct()
            {
                $this->collection = new ArrayCollection();
            }
        };
        $this->assertInstanceOf(CollectionProxy::class, $entity->collection);
    }

    public function test_should_populate_properties()
    {
        $entity = new class extends Entity {
            private int $number = 0;
            private string $string = '';
        };

        $entity->populate(['number' => 999, 'string' => 'lorem']);
        
        $this->assertEquals(999, $entity->number);
        $this->assertEquals('lorem', $entity->string);
    }
}
