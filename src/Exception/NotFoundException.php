<?php

namespace Cajudev\RestfulApi\Exception;

class NotFoundException extends HttpException
{
    public function __construct(string $message)
    {
        parent::__construct($message, 404);
    }
}
