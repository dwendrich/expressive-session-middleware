<?php

namespace SessionMiddlewareTest\Factory;

use PHPUnit\Framework\TestCase;
use SessionMiddleware\Factory\SessionMiddlewareFactory;
use SessionMiddleware\Middleware\SessionMiddleware;
use Zend\ServiceManager\ServiceManager;
use Zend\Session\SessionManager;

class SessionMiddlewareFactoryTest extends TestCase
{
    public function testCreateMiddleware()
    {
        $container = $this->prophesize(ServiceManager::class);
        $sessionManager = $this->prophesize(SessionManager::class);

        $container->get(SessionManager::class)->willReturn(
            $sessionManager->reveal()
        );

        $factory = new SessionMiddlewareFactory();
        $middleware = $factory->__invoke($container->reveal(), 'foo');

        $this->assertInstanceOf(SessionMiddleware::class, $middleware);
    }
}