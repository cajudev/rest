<?php

namespace Cajudev\Rest\Annotations\Validations;


interface AnnotationValidator
{
    public function validate($property, $value);
}
