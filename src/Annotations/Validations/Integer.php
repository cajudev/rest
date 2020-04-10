<?php

namespace Cajudev\Rest\Annotations\Validations;

use Doctrine\Common\Annotations\Annotation;

use Cajudev\Rest\Exceptions\BadRequestException;

/**
 * @Annotation
 */
final class Integer extends AbstractAnnotationValidator
{
    /**
     * @var int
     */
    public $max;

    /**
     * @var int
     */
    public $min;

    public function validate($property, $integer) {
        if (!is_int($integer)) {
            throw new BadRequestException("Parâmetro [$property] inválido");
        }

        if ($this->max !== null && $integer > $this->max) {
            throw new BadRequestException("Parâmetro [$property] ultrapassa o valor máximo ({$this->max})");
        }

        if ($this->min !== null && $integer < $this->min) {
            throw new BadRequestException("Parâmetro [$property] não atingiu o valor mínimo ({$this->min})");
        }

        return $integer;
    }
}
