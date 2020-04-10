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

    public function validate($property, $array) {
        if (!is_array($array)) {
            throw new BadRequestException("Parâmetro [$property] inválido.");
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
