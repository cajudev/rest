<?php

namespace Cajudev\RestfulApi;

use Cajudev\RestfulApi\Annotation\Validation;

use Doctrine\Common\Annotations\AnnotationReader;
use Cajudev\RestfulApi\Exception\NotFoundException;
use Cajudev\RestfulApi\Exception\BadRequestException;
use Cajudev\RestfulApi\Exception\UnprocessableEntityException;

abstract class Validator
{
    const INSERT = 1;
    const UPDATE = 2;
    const DELETE = 3;
    const READ = 4;

    public int $id = 0;
    private $reflection;

    /**
     * __construct
     *
     * @param array|object $properties - Valores a serem validados
     */
    public function __construct($properties)
    {
        $this->em           = EntityManager::getInstance();
        $this->reflection   = new \ReflectionClass($this);
        $this->annotation   = new AnnotationReader();
        $this->annotations  = $this->getAnnotations();
        foreach ($properties as $property => $value) {
            $this->setProperty($property, $value);
        }
    }

    /**
     * setProperty
     *
     * @param string $property
     * @param mixed  $value
     *
     * @return mixed
     */
    private function setProperty(string $property, $value)
    {
        try {
            $property = $this->reflection->getProperty($property);

            if (!$property->isPublic() || $property->getValue($this) !== null) {
                return null;
            }

            // Se possui um método set, faz a chamada ao método
            $method = 'set' . ucfirst($property->getName());
            if ($this->reflection->hasMethod($method)) {
                return $this->$method($value);
            }

            $property->setValue($this, $value);
        } catch (\ReflectionException $e) {
            return null;
        }
    }

    /**
     * getProperties
     *
     * Retorna todas as propriedades públicas da classe
     *
     * @return array
     */
    private function getProperties(array $exclude = []): array
    {
        return array_filter($this->reflection->getProperties(), function ($property) use ($exclude) {
            return $property->isPublic() && !in_array($property->getName(), $exclude);
        });
    }

    /**
     * getAnnotations
     *
     * Retorna todas as anotações de propriedades dos atributos da classe
     *
     * @return array
     */
    private function getAnnotations(): array
    {
        $reader = new AnnotationReader();
        foreach ($this->getProperties() as $property) {
            $ret[$property->getName()] = $reader->getPropertyAnnotation($property, Validation::class);
        }
        return $ret;
    }

    /**
     * addOptional
     *
     * Configura as propriedades recebidas como opcionais
     *
     * @param string $optionals
     *
     * @return void
     */
    public function addOptional(string ...$optionals)
    {
        foreach ($optionals as $optional) {
            $annotation = $this->annotations[$optional] ?? null;
            if ($annotation) {
                $annotation->required = false;
            }
        }
    }
        
    /**
     * addRequired
     *
     * Configura as propriedades recebidas como obrigatórias
     *
     * @return void
     */
    public function addRequired(string ...$mandatories)
    {
        foreach ($mandatories as $mandatory) {
            $annotation = $this->annotations[$mandatory] ?? null;
            if ($annotation) {
                $annotation->required = true;
            }
        }
    }

    /**
     * validateInsert
     *
     * Realiza a validação de parâmetros em casos de inserção de dados
     *
     * @return void
     */
    public function validateInsert()
    {
        foreach ($this->getProperties(['id']) as $property) {
            $annotation = $this->annotations[$property->getName()] ?? null;
            $required = $annotation ? $annotation->required : null;
            $required === null || $required === true
            ? $this->validateRequired($property)
            : $this->validateOptional($property);
        }
    }

    /**
     * validateUpdate
     *
     * Realiza a validação de parâmetros em casos de atualização de dados
     *
     * @return void
     */
    public function validateUpdate()
    {
        foreach ($this->getProperties() as $property) {
            $annotation = $this->annotations[$property->getName()] ?? null;
            $property->getName() === 'id' || $annotation && $annotation->required
            ? $this->validateRequired($property)
            : $this->validateOptional($property);
        }
    }

    /**
     * validateDelete
     *
     * Realiza a validação de parâmetros em casos de remoção de dados
     *
     * @return void
     */
    public function validateDelete()
    {
        $this->validateId();
    }

    /**
     * validateRead
     *
     * Realiza a validação de parâmetros em casos de consulta ao banco
     *
     * @return void
     */
    public function validateRead()
    {
        $this->validateId();
    }


    /**
     * validateOptional
     *
     * Realiza a validação de parâmetros apenas se os mesmos forem enviados
     *
     * @param ReflectionProperty $property
     *
     * @return void
     */
    public function validateOptional(\ReflectionProperty $property)
    {
        if ($property->getValue($this) !== null) {
            $this->validateProperty($property);
        }
    }

    /**
     * validateRequired
     *
     * Realiza a validação de parâmetros tratando-os como obrigatórios
     *
     * @param ReflectionProperty $property
     *
     * @return void
     */
    public function validateRequired(\ReflectionProperty $property)
    {
        if ($property->getValue($this) === null) {
            throw new UnprocessableEntityException("Parâmetro [{$property->getName()}] é obrigatório");
        }
        $this->validateProperty($property);
    }


    /**
     * validateProperty
     *
     * Realiza a validação de de uma propriedade através de métodos de validação e annotations
     *
     * @param ReflectionProperty $property
     *
     * @return void
     */
    public function validateProperty(\ReflectionProperty $property)
    {
        $this->validatePropertyWithAnnotation($property);
        $this->validatePropertyWithValidateMethod($property);
    }

    /**
    * validatePropertyWithValidateMethod
    *
    * Executa o método de validação correspondente, caso o mesmo exista.
    *
    * @param ReflectionProperty $property
    *
    * @return void
    */
    private function validatePropertyWithValidateMethod(\ReflectionProperty $property)
    {
        $method = 'validate' . ucfirst($property->getName());
        if ($this->reflection->hasMethod($method)) {
            $this->$method();
        }
    }

    /**
     * validatePropertyWithValidateMethod
     *
     * Executa o método de validação da annotation correspondente, caso a mesma exista.
     *
     * @param ReflectionProperty $property
     *
     * @return void
     */
    private function validatePropertyWithAnnotation(\ReflectionProperty $property)
    {
        $annotation = $this->annotation->getPropertyAnnotation($property, Validation::class);
        if ($annotation) {
            $newValue = $annotation->validate($property->getName(), $property->getValue($this));
            $property->setValue($this, $newValue);
        }
    }

    public function validateId()
    {
        if (!$this->getRepository()->findOneBy(['id' => $this->id, 'excluded' => false])) {
            throw new NotFoundException("Recurso não encontrado");
        }
    }

    /**
     * getData
     *
     * Retorna os atributos da classe em formato de array
     *
     * Caso possua uma anotação @Database(column="name") o mapeamento será realizado
     *
     * @return array
     */
    public function getData(): array
    {
        $data = [];
        foreach ($this->getProperties() as $property) {
            $annotation = $this->annotation->getPropertyAnnotation($property, Validation::class);
            $name = $annotation->rename ?? $property->getName();
            $data[$name] = $property->getValue($this);
        }
        return $data;
    }

    public function getEntity(string $name, array $params = [])
    {
        $class = str_replace('Validator', 'Entity', static::class);
        $class = preg_replace('/(\\\\)([^\\\\]+)$/', "$1{$name}", $class);
        return new $class($params);
    }

    public function getValidator(string $name, array $params = [])
    {
        $class = preg_replace('/(\\\\)([^\\\\]+)$/', "$1{$name}", static::class);
        return new $class($params);
    }

    public function getRepository(string $name = null)
    {
        $class = str_replace('Validator', 'Entity', static::class);
        if ($name) {
            $class = preg_replace('/(\\\\)([^\\\\]+)$/', "$1{$name}", $class);
        }
        return $this->em->getRepository($class);
    }
}
