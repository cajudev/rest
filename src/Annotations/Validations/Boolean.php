<?php

namespace Cajudev\Rest\Annotations\Validations;

use Doctrine\Common\Annotations\Annotation;

use Cajudev\Rest\Utils\Sanitizer;
use Cajudev\Rest\Exceptions\Http\BadRequestException;

/**
 * @Annotation
 */
final class Boolean extends AbstractAnnotationValidator
{
    /**
     * @var bool
     */
    public $strict = false;

    public function validate(string $property, $boolean, int $owner) {
        if ($this->strict && !is_bool($boolean)) {
            throw new BadRequestException("Parâmetro [$property] deve ser true ou false");
        }
        return Sanitizer::boolean($boolean);
    }
}
