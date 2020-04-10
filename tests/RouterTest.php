<?php

use PHPUnit\Framework\TestCase;

use Cajudev\Rest\App;
use Cajudev\Rest\Exceptions\MissingConfigurationException;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface as Middleware;
use Psr\Http\Server\RequestHandlerInterface as Handler;

class Test extends TestCase
{
    public function test_should_throws_when_dev_constant_not_set()
    {
        $this->expectException(MissingConfigurationException::class);
        App::create();
    }

    public function test_should_create_router_instance()
    {
        define('__DEV__', true);
        $app = App::create();
        $this->assertInstanceOf(App::class, $app);
    }
}
