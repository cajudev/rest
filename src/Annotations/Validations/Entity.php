<?php

namespace Cajudev\Rest\Annotations\Validations;

use Doctrine\Common\Annotations\Annotation;

use Cajudev\Rest\Validator;
use Cajudev\Rest\EntityManager;
use Cajudev\Rest\Factories\EntityFactory;
use Cajudev\Rest\Factories\ValidatorFactory;
use Cajudev\Rest\Factories\RepositoryFactory;
use Cajudev\Rest\Exceptions\BadRequestException;

/**
 * @Annotation
 */
final class Entity extends AbstractAnnotationValidator
{
    /**
     * @var string
     */
    public $target;


    /**
     * @var string
     */
    public $field = null;

    /**
     * @var bool
     */
    public $exclusive = false;

    public function __construct(array $params) {
        $this->owner = strtolower($params['owner']);
        $this->target = strtolower($params['target']);
        $this->field = $params['field'] ?? null;
        $this->exclusive = $params['exclusive'] ?? false;
    }

    public function validate($property, $value, $owner) {
        $repository = RepositoryFactory::make($this->target);

        if (is_int($value)) {
            if ($entity = $repository->find($value)) {
                return $entity;
            }
            throw new BadRequestException("Recurso {$this->target} [$value] não encontrado");
        }

        if (is_object($value)) {
            $validator = ValidatorFactory::make($this->target, $value);

            if (isset($value->id)) {
                if ($entity = $repository->find($value->id)) {
                    $validator->validate(Validator::UPDATE);
                    $entity->populate($validator->payload());
                    return $entity;
                }
                throw new BadRequestException("Recurso {$this->target} [$value->id] não encontrado");
            }

            $validator->validate(Validator::INSERT);
            return EntityFactory::make($this->target, $validator->payload());
        }

        if (is_string($value) && $this->field) {
            $params = [$this->field => $value];

            if ($this->exclusive) {
                $search[strtolower($this->owner)] = $owner;
            }
    
            if ($entity = $repository->findOneBy($params)) {
                return $entity;
            }

            $validator = ValidatorFactory::make($this->target, $params);
            $validator->validate(Validator::INSERT);

            return EntityFactory::make($this->target, $validator->payload());
        }

        throw new BadRequestException("Parâmetro {$this->target} inválido");
    }
}
