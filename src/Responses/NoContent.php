<?php

namespace Cajudev\Rest\Responses;

use Psr\Http\Message\ResponseInterface as Response;

class NoContent extends AppResponse
{
    public function __construct(Response $response) {
        parent::__construct($response, [], 204);
    }
}
