<?php

namespace Cajudev\Rest\Annotations\Validations;

use Doctrine\Common\Annotations\Annotation;

use Cajudev\Rest\Exceptions\BadRequestException;

/**
 * @Annotation
 */
final class Mixed extends AbstractAnnotationValidator
{
    /**
     * @var array
     */
    public $types;

    public function validate(string $property, $mixed, int $owner) {
        if (!in_array(gettype($mixed), $this->types)) {
            $types = preg_replace('/(.+), /', '\1 e ', implode(', ', $this->types));
            throw new BadRequestException("Parâmetro [$property] inválido. Tipos permitidos são: {$types}");
        }

        return $mixed;
    }
}
