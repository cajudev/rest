<?php

namespace Cajudev\RestfulApi;

use Slim\App;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Slim\Factory\AppFactory;
use Slim\Middleware\ErrorMiddleware;

use Cajudev\RestfulApi\Service;
use Cajudev\RestfulApi\Exception\MissingConfigurationException;

use Psr\Http\Server\MiddlewareInterface as Middleware;

class Router
{
    private $app;
    private $errorMiddleware;

    public function __construct()
    {
        if (!defined('__DEV__')) {
            throw new MissingConfigurationException('A environment constant called __DEV__ must be defined');
        }
        $this->app = AppFactory::create();
        $this->errorMiddleware = $this->app->addErrorMiddleware(__DEV__, true, true);
    }

    public static function create()
    {
        return new Router();
    }

    public function addMiddleware(Middleware $middleware)
    {
        $this->app->add($middleware);
    }

    public function setErrorHandler(\Closure $errorHandler)
    {
        $this->errorMiddleware->setDefaultErrorHandler($errorHandler);
    }

    public function addEndpoint(string $endpoint, Service $service)
    {
        $this->app->get("/{$endpoint}/{id}", function (Request $request, Response $response, array $args) use ($service) {
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
        $this->app->put("/{$endpoint}/{id}", function (Request $request, Response $response, array $args) use ($service) {
            $response = $service->update($request, $response, $args);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        });
        $this->app->delete("/{$endpoint}/{id}", function (Request $request, Response $response, array $args) use ($service) {
            $response = $service->delete($request, $response, $args);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        });
    }

    public function __call($method, $args)
    {
        return $this->app->$method(...$args);
    }
}
