<?php

namespace Cajudev\Rest\Annotations;

/** @Annotation */
final class Validation
{
    /** @var string */
    public $type;

    /** @var boolean */
    public $required;

    /** @var string */
    public $rename;

    /** @var array */
    public $params;

    public function validate($property, $value)
    {
        $class = 'App\\Validator\\Annotation\\' . ucfirst($this->type) . 'Validator';
        $validator = new $class($this->params);
        return $validator->validate($property, $value);
    }
}