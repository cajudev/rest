<?php

namespace Cajudev\Rest\Annotations\Validations;

use Doctrine\Common\Annotations\Annotation;

use Cajudev\Rest\Exceptions\Http\BadRequestException;

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

    public function validate(string $property, $string, int $owner) {
        if (!is_string($string)) {
            throw new BadRequestException("Parâmetro [$property] inválido");
        }

        if ($this->length !== null && strlen($string) !== $this->length) {
            throw new BadRequestException(
                sprintf("Parâmetro [%s] deve possuir exatamente %d %s.", $property, $this->length, $this->length > 1 ? 'caracteres' : 'caractere')
            );
        }

        if ($this->maxlength !== null && strlen($string) > $this->maxlength) {
            throw new BadRequestException(
                sprintf("Parâmetro [%s] deve possuir no máximo %d %s.", $property, $this->maxlength, $this->maxlength > 1 ? 'caracteres' : 'caractere')
            );
        }

        if ($this->minlength !== null && strlen($string) < $this->minlength) {
            throw new BadRequestException(
                sprintf("Parâmetro [%s] deve possuir no mínimo %d %s.", $property, $this->minlength, $this->minlength > 1 ? 'caracteres' : 'caractere')
            );
        }

        return $string;
    }
}
