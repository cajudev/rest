<?php

namespace Cajudev\Rest;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Annotations\AnnotationReader;

use Cajudev\Rest\Annotations\Query;
use Cajudev\Rest\Annotations\Payload;
use Cajudev\Rest\Collections\CollectionProxy;

/**
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 */
abstract class Entity
{
    /**
     * @Payload
     * 
     * @Query(sortable=true)
     * 
     * @ORM\Id @ORM\Column(type="integer") @ORM\GeneratedValue
     */
    protected ?int $id = 0;

    /**
     * @Payload
     * 
     * @ORM\Column(type="boolean", nullable=false)
     */
    protected bool $active = true;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    protected bool $excluded = false;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    protected \DateTime $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
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

    /**
     * Return the payload representation of the entity
     *
     * @return object
     */
    public function payload(): object {
        $reader  = new AnnotationReader();
        $payload = new \StdClass();

        foreach ($this->getReflection()->getProperties() as $property) {
            if ($annotation = $reader->getPropertyAnnotation($property, Payload::class)) {
                $payload->{$property->getName()} = $this->addPayloadByAnnotation($annotation, $property);
            }
        }
        
        return $payload;
    }

    private function addPayloadByAnnotation(Payload $annotation, \ReflectionProperty $property) {
        $property->setAccessible(true);

        if ($property->getValue($this) instanceof Entity) {
            return $this->addPayloadByAnnotationUsingEntity($annotation, $property->getValue($this));
        }
        
        if ($property->getValue($this) instanceof Collection) {
            return $this->addPayloadByAnnotationUsingCollection($annotation, $property->getValue($this));
        }

        return $property->getValue($this);
    }

    private function addPayloadByAnnotationUsingEntity(Payload $annotation, Entity $entity) {
        if ($annotation->property) {
            return $this->addPayloadByProperties($annotation, [$annotation->property], $entity);
        }

        if ($annotation->properties) {
            return $this->addPayloadByProperties($annotation, $annotation->properties, $entity);
        }

        return $entity->payload();
    }

    private function addPayloadByProperties($annotation, $properties, $entity) {
        $return = new \StdClass();

        foreach ($properties as $key => $property) {
            $this->addPayloadByProperty($return, $annotation, $key, $property, $entity);
        }

        if (count($properties) === 1) {
            $return = current($return);
        }

        return $return;
    }

    private function addPayloadByProperty($return, $annotation, $key, $property, $entity) {
        if (property_exists($entity, $key) && $entity->$key instanceof Entity) {
            return $return->$key = $this->addPayloadByProperties($annotation, $property, $entity->$key);
        }
        
        if ($entity->$property instanceof $entity) {    
            return $return->$property = $this->addPayloadByAnnotationUsingEntity($annotation, $entity->$property);
        }
        
        if ($entity->$property instanceof Entity) {
            return $return->$property = $entity->$property->payload();
        }

        $return->$property = $entity->$property;
    }

    private function addPayloadByAnnotationUsingCollection(Payload $annotation, Collection $entities) {
        $return = [];

        foreach ($entities as $key => $entity) {
            $return[$key] = $this->addPayloadByAnnotationUsingEntity($annotation, $entity);
        }

        return $return;
    }

    public function __get($property)
    {
        $ref = $this->getReflection()->getProperty(strtolower($property));
        $ref->setAccessible(true);
        $value = $ref->getValue($this);

        if ($value instanceof Collection) {
            return new CollectionProxy($this, strtolower($property), $value);
        }

        return $value;
    }

    public function __set($property, $value)
    {
        $ref = $this->getReflection()->getProperty(strtolower($property));
        $ref->setAccessible(true);
    
        if ($value instanceof Collection) {
            $ref->setValue($this, $ref->isInitialized($this) ? $ref->getValue($this) : new ArrayCollection());
            $proxy = new CollectionProxy($this, $ref->getValue($this));
            $proxy->set($value);
        } else {
            $ref->setValue($this, $value);
        }
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
