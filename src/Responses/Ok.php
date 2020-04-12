<?php

namespace Cajudev\Rest\Responses;

use Psr\Http\Message\ResponseInterface as Response;

class Ok extends AppResponse
{
    public function __construct(Response $response, $content) {
        parent::__construct($response, $content, 200);
    }
}
