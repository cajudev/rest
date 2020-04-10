<?php

namespace Cajudev\Rest\Annotations\Validations;

use Doctrine\Common\Annotations\Annotation;

use Cajudev\Rest\Exceptions\BadRequestException;

/**
 * @Annotation
 */
final class Arrays implements AnnotationValidator
{
    /**
     * @var array
     */
    public $types;

    /**
     * @var int
     */
    public $length;

    /**
     * @var int
     */
    public $minlength;

    /**
     * @var int
     */
    public $maxlength;

    public function validate($property, $array) {
        if (!is_array($array)) {
            throw new BadRequestException("Parâmetro [$property] inválido.");
        }

        if ($this->length !== null && count($array) !== $this->length) {
            throw new BadRequestException("Parâmetro [$property] deve possuir exatamente {$this->length} itens.");
        }

        if ($this->minlength !== null && count($array) < $this->minlength) {
            throw new BadRequestException("Parâmetro [$property] deve possuir no mínimo {$this->minlength} itens.");
        }

        if ($this->maxlength !== null && count($array) > $this->maxlength) {
            throw new BadRequestException("Parâmetro [$property] deve possuir no máximo {$this->maxlength} itens.");
        }

        foreach ($array as $key => $value) {
            if (!in_array(gettype($value), $this->types)) {
                $types = preg_replace('/(.+), /', '\1 e ', implode(', ', $this->types));
                throw new BadRequestException(sprintf("Item [%s] do parâmetro [%s] inválido. Tipos permitidos são: %s", ++$key, $property, $types));
            }   
        }

        return $array;
    }
}
