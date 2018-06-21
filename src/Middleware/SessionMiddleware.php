<?php

declare(strict_types=1);

namespace SessionMiddleware\Middleware;

use Psr\Http\Server\RequestHandlerInterface as DelegateInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Session\SessionManager;

/**
 * Class SessionMiddleware
 *
 * @package SessionMiddleware\Middleware
 * @author Daniel Wendrich <daniel.wendrich@gmail.com>
 */
class SessionMiddleware implements MiddlewareInterface
{
    const SESSION_ATTRIBUTE = self::class . '::session_manager';

    /**
     * @var SessionManager
     */
    private $sessionManager;

    /**
     * SessionMiddleware constructor.
     *
     * @param SessionManager $sessionManager
     */
    public function __construct(SessionManager $sessionManager)
    {
        $this->sessionManager = $sessionManager;
    }

    /**
     * Process an incoming server request and return a response, optionally delegating
     * to the next middleware component to create the response.
     *
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate) : ResponseInterface
    {
        // start session handling
        $this->sessionManager->start();

        // call next middleware in stack and directly return response
        return $delegate->handle(
            // pass on session manager as request attribute
            $request->withAttribute(self::SESSION_ATTRIBUTE, $this->sessionManager)
        );
    }
}
