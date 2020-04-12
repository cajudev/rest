<?php

namespace Cajudev\Rest\Exceptions;

class TooManyRequestsException extends HttpException
{
    public function __construct(string $message, string $hint = '')
    {
        parent::__construct($message, $hint, 429);
    }
}
