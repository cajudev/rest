<?php

namespace Cajudev\Rest\Exceptions\Http;

class ForbiddenException extends HttpException
{
    public function __construct(string $message, string $hint = '')
    {
        parent::__construct($message, $hint, 401);
    }
}
