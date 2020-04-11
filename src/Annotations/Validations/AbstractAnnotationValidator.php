<?php

namespace Cajudev\Rest\Annotations\Validations;

use Doctrine\Common\Annotations\Annotation;

use Cajudev\Rest\Exceptions\BadRequestException;

abstract class AbstractAnnotationValidator implements AnnotationValidator
{
    /**
     * @var bool
     */
    public $required;

    /**
     * @var string
     */
    public $rename;
}