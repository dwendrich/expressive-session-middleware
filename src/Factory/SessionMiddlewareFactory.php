<?php

namespace SessionMiddleware\Factory;

use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\Session\SessionManager;
use SessionMiddleware\Middleware\SessionMiddleware;

/**
 * Class SessionMiddlewareFactory
 *
 * @package SessionMiddleware\Factory
 * @author Daniel Wendrich <daniel.wendrich@gmail.com>
 */
class SessionMiddlewareFactory implements FactoryInterface
{
    /**
     * Create an object
     *
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return object
     * @throws ServiceNotFoundException if unable to resolve the service.
     * @throws ServiceNotCreatedException if an exception is raised when
     *     creating a service.
     * @throws ContainerException if any other error occurs
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $sessionManager = $container->get(SessionManager::class);
        return new SessionMiddleware($sessionManager);
    }
}
