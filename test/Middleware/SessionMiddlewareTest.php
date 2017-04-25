<?php

namespace SessionMiddlewareTest\Middleware;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use SessionMiddleware\Middleware\SessionMiddleware;
use Zend\Session\SessionManager;

class SessionMiddlewareTest extends TestCase
{
    public function testSessionStartIsCalledAndRequestIsDelegated()
    {
        $invoked = false;

        $request = $this->prophesize(ServerRequestInterface::class);
        $request->withAttribute(
            SessionMiddleware::REQUEST_ATTRIBUTE_KEY,
            Argument::type(SessionManager::class)
        )->willReturn(
            $request->reveal()
        );

        $response = $this->prophesize(ResponseInterface::class)->reveal();
        $delegate = $this->prophesize(DelegateInterface::class);

        $delegate->process(Argument::any())->will(function () use(&$invoked, $response) {
            $invoked = true;
            return $response;
        });

        $sessionManager = $this->prophesize(SessionManager::class);
        $sessionManager->start()->willReturn(true);

        // assert that $sessionManager->start() gets called
        $sessionManager->start()->shouldbeCalled();

        // assert that $sessionManager instance is added as request attribute
        $request->withAttribute(Argument::cetera())->shouldBeCalled();

        $middleware = new SessionMiddleware($sessionManager->reveal());
        $return = $middleware->process($request->reveal(), $delegate->reveal());

        $this->assertSame($response, $return);
        $this->assertTrue($invoked);
    }
}