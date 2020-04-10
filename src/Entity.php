<?php

namespace Cajudev\Rest;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Annotations\AnnotationReader;

use Rest\Annotation\Payload;

/**
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 */
abstract class Entity
{
    /**
     * @Payload
     * 
     * @ORM\Id @ORM\Column(type="integer") @ORM\GeneratedValue
     */
    protected int $id = 0;

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
            return $entity->{$annotation->property};
        }

        if ($annotation->properties) {
            $return = new \StdClass();

            foreach ($annotation->properties as $property) {
                $return->$property = $entity->$property;
            }

            return $return;
        }

        return $entity->payload();
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
