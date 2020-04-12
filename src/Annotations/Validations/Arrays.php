<?php

namespace Cajudev\Rest\Annotations\Validations;

use Doctrine\Common\Annotations\Annotation;

use Cajudev\Rest\Exceptions\BadRequestException;

/**
 * @Annotation
 */
final class Arrays extends AbstractAnnotationValidator
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
    public $minlength = 1;

    /**
     * @var int
     */
    public $maxlength;

    public function validate(string $property, $array, int $owner) {
        if (!is_array($array)) {
            throw new BadRequestException("Parâmetro [$property] inválido.");
        }

        if ($this->length !== null && count($array) !== $this->length) {
            throw new BadRequestException(
                sprintf("Parâmetro [%s] deve possuir exatamente %d %s.", $property, $this->length, $this->length > 1 ? 'itens' : 'item')
            );
        }

        if ($this->minlength !== null && count($array) < $this->minlength) {
            throw new BadRequestException(
                sprintf("Parâmetro [%s] deve possuir no mínimo %d %s.", $property, $this->minlength, $this->minlength > 1 ? 'itens' : 'item')
            );
        }

        if ($this->maxlength !== null && count($array) > $this->maxlength) {
            throw new BadRequestException(
                sprintf("Parâmetro [%s] deve possuir no máximo %d %s.", $property, $this->maxlength, $this->maxlength > 1 ? 'itens' : 'item')
            );
        }

        foreach ($array as $key => $value) {
            if (!in_array(gettype($value), $this->types)) {
                $types = preg_replace('/(.+), /', '\1 e ', implode(', ', $this->types));
                throw new BadRequestException(
                    sprintf("Item [%s] do parâmetro [%s] inválido. Tipos permitidos são: %s", ++$key, $property, $types)
                );
            }   
        }

        return $array;
    }
}
