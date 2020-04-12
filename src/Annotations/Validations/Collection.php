<?php

namespace Cajudev\Rest\Annotations\Validations;

use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Collections\ArrayCollection;

use Cajudev\Rest\Validator;
use Cajudev\Rest\EntityManager;
use Cajudev\Rest\Factories\EntityFactory;
use Cajudev\Rest\Factories\ValidatorFactory;
use Cajudev\Rest\Factories\RepositoryFactory;
use Cajudev\Rest\Exceptions\Http\BadRequestException;

/**
 * @Annotation
 */
final class Collection extends AbstractAnnotationValidator
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

    public function validate($property, $values, $owner) {
        if (!is_array($values)) {
            throw new BadRequestException("Parâmetro [$property] deve ser uma lista de itens");
        }

        if ($this->required && count($values) === 0) {
            throw new BadRequestException("Parâmetro [$property] não pode ser uma lista vazia", "Isso ocorre quando você configura uma collection como obrigatória na classe de validação");
        }

        $entity = new Entity();
        $entity->owner = $this->owner;
        $entity->required = $this->required;
        $entity->rename = $this->rename;
        $entity->owner = $this->owner;
        $entity->target = $this->target;
        $entity->field = $this->field;
        $entity->exclusive = $this->exclusive;

        $ret = new ArrayCollection();

        foreach ($values as $key => $value) {
            $ret[$key] = $entity->validate($property, $value, $owner);
        }

        return $ret;
    }
}
