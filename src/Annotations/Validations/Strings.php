<?php

namespace Cajudev\Rest\Annotations\Validations;

use Doctrine\Common\Annotations\Annotation;

use Cajudev\Rest\Exceptions\BadRequestException;

/**
 * @Annotation
 */
final class Strings extends AbstractAnnotationValidator
{
    /**
     * @var int
     */
    public $length;

    /**
     * @var int
     */
    public $maxlength;

    /**
     * @var int
     */
    public $minlength;

    public function validate($property, $string) {
        if (!is_string($string)) {
            throw new BadRequestException("Parâmetro [$property] inválido");
        }

        if ($this->length !== null && strlen($string) !== $this->length) {
            throw new BadRequestException("Parâmetro [$property] deve possuir exatamente {$this->length} caracteres");
        }

        if ($this->maxlength !== null && strlen($string) > $this->maxlength) {
            throw new BadRequestException("Parâmetro [$property] deve possuir no máximo {$this->maxlength} caracteres");
        }

        if ($this->minlength !== null && strlen($string) < $this->minlength) {
            throw new BadRequestException("Parâmetro [$property] deve possuir no mínimo {$this->minlength} caracteres");
        }

        return $string;
    }
}
