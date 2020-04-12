<?php

namespace Cajudev\Rest\Exceptions\Http;

class HttpException extends \Exception
{
    protected string $hint;

    public function __construct(string $message, string $hint = 'Desculpe, nenhuma dica sobre esse erro foi adicionada ainda', int $code)
    {
        parent::__construct($message, $code);
        $this->hint = $hint;
    }

    public function getHint(): string {
        return $this->hint;
    }
}
