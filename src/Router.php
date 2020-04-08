<?php

namespace Cajudev\RestfulApi;

use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Middleware\ErrorMiddleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface as Middleware;

use Cajudev\RestfulApi\Service;
use Cajudev\RestfulApi\Middleware\JsonMiddleware;
use Cajudev\RestfulApi\Exception\MissingConfigurationException;

class Router
{
    private $app;
    private $errorMiddleware;

    private function __construct()
    {
        if (!defined('__ROOT__')) {
            throw new MissingConfigurationException('A environment constant called __ROOT__ must be defined');
        }
        if (!defined('__DEV__')) {
            throw new MissingConfigurationException('A environment constant called __DEV__ must be defined');
        }
        $this->app = AppFactory::create();
        $this->app->addRoutingMiddleware();
        $this->app->add(new JsonMiddleware());
        $this->setErrorHandler();
    }

    public static function create(): Router
    {
        return new Router();
    }

    public function addMiddleware(Middleware $middleware): void
    {
        $this->app->add($middleware);
    }

    public function crud(string $endpoint, Service $service)
    {
        $this->app->get("/{$endpoint}/{id:[0-9]+}", function (Request $request, Response $response, array $args) use ($service) {
            $response = $service->getOne($request, $response, $args);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        });
        $this->app->get("/{$endpoint}", function (Request $request, Response $response, array $args) use ($service) {
            $response = $service->getAll($request, $response, $args);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        });
        $this->app->post("/{$endpoint}", function (Request $request, Response $response, array $args) use ($service) {
            $response = $service->insert($request, $response, $args);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
        });
        $this->app->put("/{$endpoint}/{id:[0-9]+}", function (Request $request, Response $response, array $args) use ($service) {
            $response = $service->update($request, $response, $args);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        });
        $this->app->delete("/{$endpoint}/{id:[0-9]+}", function (Request $request, Response $response, array $args) use ($service) {
            $response = $service->delete($request, $response, $args);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(204);
        });
    }

    public function __call($method, $args)
    {
        return $this->app->$method(...$args);
    }

    private function setErrorHandler(): void
    {
        $router = $this->app;
        
        $this->errorMiddleware = $this->app->addErrorMiddleware(__DEV__, true, true);

        $this->errorMiddleware->setDefaultErrorHandler(function ($request, $e) use ($router) {
            $response = $router->getResponseFactory()->createResponse();
            $data = ['error' => $e->getMessage()];
            $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            return $response->withHeader('Content-Type', 'application/json')->withStatus($e->getCode());
        });
    }
}
