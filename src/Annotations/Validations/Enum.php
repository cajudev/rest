<?php

namespace Cajudev\Rest\Annotations\Validations;

use Doctrine\Common\Annotations\Annotation;

use Cajudev\Rest\Exceptions\Http\BadRequestException;

/**
 * @Annotation
 */
final class Enum extends AbstractAnnotationValidator
{
    /**
     * @var array
     */
    public $values;

    public function validate(string $property, $element, int $owner) {
        if (!is_int($element) && !is_string($element)) {
            throw new BadRequestException("Parâmetro [$property] inválido");
        }

        if (!in_array($element, $this->values)) {
            $values = preg_replace('/(.+), /', '\1 e ', implode(', ', $this->values));
            throw new BadRequestException("Parâmetro [$property] inválido. Os valores permitidos são: {$values}");
        }

        return $element;
    }
}
