<?php

namespace Cajudev\Rest\Exceptions\Http;

class UnprocessableEntityException extends HttpException
{
    public function __construct(string $message, string $hint = '')
    {
        parent::__construct($message, $hint, 422);
    }
}
