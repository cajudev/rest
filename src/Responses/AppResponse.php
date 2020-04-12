<?php

namespace Cajudev\Rest\Responses;

use Psr\Http\Message\StreamInterface as Stream;
use Psr\Http\Message\ResponseInterface as Response;

abstract class AppResponse implements Response
{
    protected Response $response;

    public function __construct(Response $response, $content = [], int $code) {
        $response->getBody()->write(json_encode($content, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        $this->response = $response->withHeader('Content-Type', 'application/json')->withStatus($code);
    }

    public function getProtocolVersion() {
        return $this->response->getProtocolVersion();
    }

    public function withProtocolVersion($version) {
        return $this->response->withProtocolVersion($version);
    }

    public function getHeaders() {
        return $this->response->getHeaders();
    }

    public function hasHeader($name) {
        return $this->response->hasHeader($name);
    }

    public function getHeader($name) {
        return $this->response->getHeader($name);
    }

    public function getHeaderLine($name) {
        return $this->response->getHeaderLine($name);
    }

    public function withHeader($name, $value) {
        return $this->response->withHeader($name, $value);
    }

    public function withAddedHeader($name, $value) {
        return $this->response->withAddedHeader($name, $value);
    }

    public function withoutHeader($name) {
        return $this->response->withoutHeader($name);
    }

    public function getBody() {
        return $this->response->getBody();
    }

    public function withBody(Stream $body) {
        return $this->response->withBody($body);
    }

    public function getStatusCode() {
        return $this->response->getStatusCode();
    }

    public function withStatus($code, $reasonPhrase = '') {
        return $this->response->withStatus($code, $reasonPhrase);
    }

    public function getReasonPhrase() {
        return $this->response->getReasonPhrase();
    }
}
