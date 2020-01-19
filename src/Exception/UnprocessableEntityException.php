<?php

namespace Cajudev\RestfulApi\Exception;

class UnprocessableEntityException extends HttpException
{
    public function __construct(string $message)
    {
        parent::__construct($message, 422);
    }
}
