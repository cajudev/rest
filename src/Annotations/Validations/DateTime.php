<?php

namespace Cajudev\Rest\Annotations\Validations;

use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\Annotation\Enum;

use Cajudev\Rest\Exceptions\BadRequestException;

/**
 * @Annotation
 */
final class DateTime extends AbstractAnnotationValidator
{
    /**
     * @Enum({"ATOM", "COOKIE", "ISO8601", "RFC822", "RFC850", "RFC1036", "RFC1123", "RFC2822", "RFC3339", "RSS", "W3C"})
     */
    public $format;

    public function validate(string $property, $string, int $owner) {
        $hint = 'Você pode trocar o formato na classe de validação correspondente, alterando o parâmetro [format] da anotação DateTime';

        if (!(is_string($string))) {
            throw new BadRequestException("Parâmetro [$property] inválido. O formato aceito é {$this->format}", $hint);
        }

        if (!($datetime = \DateTime::createFromFormat(constant("\DateTime::{$this->format}"), $string))) {
            throw new BadRequestException("Parâmetro [$property] inválido. O formato aceito é {$this->format}", $hint);
        }
        
        return $datetime;
    }
}
