<?php

namespace Cajudev\RestfulApi;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;

/**
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 */
abstract class Entity
{
    /** @ORM\Id @ORM\Column(type="integer") @ORM\GeneratedValue **/
    protected int $id = 0;

    /** @ORM\Column(type="boolean", nullable=false) */
    protected bool $active = true;

    /** @ORM\Column(type="boolean", nullable=false) */
    protected bool $excluded = false;

    /** @ORM\Column(type="datetime", nullable=false) */
    protected \DateTime $createdAt;

    /** @ORM\Column(type="datetime", nullable=false) */
    protected \DateTime $updatedAt;

    public function __construct($properties = [])
    {
        $this->populate($properties);
    }

    public function populate($properties = [])
    {
        foreach ($properties as $property => $value) {
            $this->$property = $value;
        }
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist(): void
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    /**
     * @ORM\PreUpdate
     */
    public function preUpdate(): void
    {
        $this->updatedAt = new \DateTime();
    }

    public function __get($property)
    {
        $ref = $this->getReflection()->getProperty(strtolower($property));
        $ref->setAccessible(true);
        $value = $ref->getValue($this);

        if ($value instanceof Collection) {
            return new CollectionProxy($this, $value);
        }

        return $value;
    }

    public function __set($property, $value)
    {
        $ref = $this->getReflection()->getProperty(strtolower($property));
        $ref->setAccessible(true);
    
        if ($value instanceof Collection) {
            $proxy = new CollectionProxy($this, $value);
        }

        $ref->setValue($this, $value);
    }

    private function getReflection(): \ReflectionClass
    {
        $reflection = new \ReflectionClass($this);

        if ($this instanceof \Doctrine\Common\Persistence\Proxy) {
            $reflection = $reflection->getParentClass();
        }

        return $reflection;
    }
}
