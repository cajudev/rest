<?php

namespace Cajudev\Rest\Annotations\Validations;


interface AnnotationValidator
{
    public function validate(string $property, $value, int $owner);
}
