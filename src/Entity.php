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

    /**
     * @param Payload $annotation
     * @param \ReflectionProperty $property
     */
    private function addPayloadByAnnotation(Payload $annotation, \ReflectionProperty $property) {
        $property->setAccessible(true);

        if ($property->getValue($this) instanceof Entity) {
            return $this->addPayloadByAnnotationUsingEntity($annotation, $property->getValue($this));
        }
        
        if ($property->getValue($this) instanceof Collection) {
            return $this->addPayloadByAnnotationUsingCollection($annotation, $property->getValue($this));
        }

        if ($property->getValue($this) instanceof \DateTime) {
            return $this->addPayloadByAnnotationUsingDateTime($annotation, $property->getValue($this));
        }

        return $property->getValue($this);
    }

    /**
     * @param Payload $annotation
     * @param Entity $entity
     */
    private function addPayloadByAnnotationUsingEntity(Payload $annotation, Entity $entity) {
        if ($annotation->property) {
            return $this->addPayloadByProperties($annotation, [$annotation->property], $entity);
        }

        if ($annotation->properties) {
            return $this->addPayloadByProperties($annotation, $annotation->properties, $entity);
        }

        return $entity->payload();
    }

    /**
     * @param Payload $annotation
     * @param array $properties
     * @param Entity $entity
     */
    private function addPayloadByProperties(Payload $annotation, array $properties, Entity $entity) {
        $return = new \StdClass();

        foreach ($properties as $key => $property) {
            $this->addPayloadByProperty($return, $annotation, $key, $property, $entity);
        }

        if (count($properties) === 1) {
            $return = current($return);
        }

        return $return;
    }

    /**
     * @param object $return
     * @param Payload $annotation
     * @param string $key
     * @param mixed $property
     * @param Entity $entity
     */
    private function addPayloadByProperty(object $return, Payload $annotation, string $key, $property, Entity $entity) {
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

    /**
     * @param Payload $annotation
     * @param Collection $entities
     */
    private function addPayloadByAnnotationUsingCollection(Payload $annotation, Collection $entities) {
        $return = [];

        foreach ($entities as $key => $entity) {
            $return[$key] = $this->addPayloadByAnnotationUsingEntity($annotation, $entity);
        }

        return $return;
    }

    /**
     * @param Payload $annotation
     * @param \DateTime $datetime
     */
    private function addPayloadByAnnotationUsingDatetime(Payload $annotation, \DateTime $datetime) {
        $formats = ["ATOM", "COOKIE", "ISO8601", "RFC822", "RFC850", "RFC1036", "RFC1123", "RFC2822", "RFC3339", "RSS", "W3C"];
        $format = in_array($annotation->format, $formats) ? constant("\DateTime::{$annotation->format}") : $annotation->format;
        return $datetime->format($format);
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
