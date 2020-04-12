<?php

namespace Cajudev\Rest;

use Doctrine\Common\Annotations\AnnotationReader;

use Cajudev\Rest\Factories\RepositoryFactory;

use Cajudev\Rest\Annotations\Validations\AnnotationValidator;

use Cajudev\Rest\Exceptions\Http\NotFoundException;
use Cajudev\Rest\Exceptions\Http\BadRequestException;
use Cajudev\Rest\Exceptions\Http\UnprocessableEntityException;

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
        $this->reflection   = new \ReflectionClass($this);
        $this->annotation   = new AnnotationReader();
        $this->annotations  = $this->getAnnotations();
        $this->setProperties($properties);
    }

    /**
     *
     * @param array|object $properties
     */
    private function setProperties($properties)
    {
        foreach ($properties as $property => $value) {
            $this->setProperty($property, $value);
        }
    }

    /**
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

            if (!$property->isPublic()) {
                return null;
            }

            $property->setValue($this, $value);
        } catch (\ReflectionException $e) {
            return null;
        }
    }

    /**
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
     * Retorna todas as anotações de propriedades dos atributos da classe
     *
     * @return array
     */
    private function getAnnotations(): array
    {
        $reader = new AnnotationReader();
        foreach ($this->getProperties() as $property) {
            $ret[$property->getName()] = $reader->getPropertyAnnotation($property, AnnotationValidator::class);
        }
        return $ret;
    }

    /**
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
     * Realiza a validação de parâmetros em casos de inserção de dados
     *
     * @return void
     */
    public function validate(int $action)
    {
        switch ($action) {
            case self::READ: $this->validateRead(); break;
            case self::INSERT: $this->validateInsert(); break;
            case self::UPDATE: $this->validateUpdate(); break;
            case self::DELETE: $this->validateDelete(); break;
        }
    }

    /**
     * Realiza a validação de parâmetros em casos de inserção de dados
     *
     * @return void
     */
    private function validateInsert()
    {
        foreach ($this->getProperties(['id']) as $property) {
            $annotation = $this->annotations[$property->getName()] ?? null;
            $required = $annotation ? $annotation->required : null;
            $required ? $this->validateRequired($property) : $this->validateOptional($property);
        }
    }

    /**
     * Realiza a validação de parâmetros em casos de atualização de dados
     *
     * @return void
     */
    private function validateUpdate()
    {
        foreach ($this->getProperties() as $property) {
            $property->getName() === 'id' ? $this->validateRequired($property) : $this->validateOptional($property);
        }
    }

    /**
     * Realiza a validação de parâmetros em casos de remoção de dados
     *
     * @return void
     */
    private function validateDelete()
    {
        $this->validateId();
    }

    /**
     * Realiza a validação de parâmetros em casos de consulta ao banco
     *
     * @return void
     */
    private function validateRead()
    {
        $this->validateId();
    }


    /**
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
     * Executa o método de validação da annotation correspondente, caso a mesma exista.
     *
     * @param ReflectionProperty $property
     *
     * @return void
     */
    private function validatePropertyWithAnnotation(\ReflectionProperty $property)
    {
        $annotation = $this->annotation->getPropertyAnnotation($property, AnnotationValidator::class);
        if ($annotation) {
            $newValue = $annotation->validate($property->getName(), $property->getValue($this), $this->id);
            $property->setValue($this, $newValue);
        }
    }

    public function validateId()
    {
        $repository = RepositoryFactory::make($this->reflection->getShortName());
        if (!$repository->find($this->id)) {
            throw new NotFoundException("Recurso não encontrado", "Verifique se o identificador informado é válido, ou se o recurso já foi excluído.");
        }
    }

    /**
     * Retorna todos os atributos da classe
     *
     * @return object
     */
    public function payload(): object
    {
        $payload = new \StdClass();
        foreach ($this->getProperties() as $property) {
            if ($property->getValue($this) !== null) {
                $annotation = $this->annotations[$property->getName()] ?? null;
                $name = $annotation->rename ?? $property->getName();
                $payload->$name = $property->getValue($this);
            }
        }
        return $payload;
    }
}
