<?php

namespace SessionMiddleware\Factory;

use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\Session\Config\ConfigInterface;
use Zend\Session\Config\SessionConfig;
use Zend\Session\Container;
use Zend\Session\SaveHandler\SaveHandlerInterface;
use Zend\Session\SessionManager;
use Zend\Session\Storage\StorageInterface;

/**
 * Class SessionManagerFactory
 *
 * @package SessionMiddleware\Factory
 * @author Daniel Wendrich <daniel.wendrich@gmail.com>
 */
class SessionManagerFactory implements FactoryInterface
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
        $config = $container->get('config');

        if (!isset($config['session'])) {
            $sessionManager = new SessionManager();
            Container::setDefaultManager($sessionManager);
            return $sessionManager;
        }

        $session = $config['session'];

        $sessionConfig = null;
        if (isset($session['config'])) {
            $class = isset($session['config']['class'])
                ? $session['config']['class']
                : SessionConfig::class;

            $options = isset($session['config']['options'])
                ? $session['config']['options']
                : [];


            $sessionConfig = new $class();

            if (!($sessionConfig instanceof ConfigInterface)) {
                throw new \InvalidArgumentException(
                    'Session config must implement Zend\Session\Config\ConfigInterface.'
                );
            }

            $sessionConfig->setOptions($options);
        }

        $sessionStorage = null;
        if (isset($session['storage'])) {
            $class = $session['storage'];

            if (is_string($session['storage'])) {
                $class = new $session['storage']();
            }

            if (!($class instanceof StorageInterface)) {
                throw new \InvalidArgumentException(
                    'Session storage must implement Zend\Session\Storage\StorageInterface.'
                );
            }

            $sessionStorage = $class;
        }

        $sessionSaveHandler = null;
        if (isset($session['save_handler'])) {
            $class = $session['save_handler'];

            if (is_string($session['save_handler'])) {
                // class should be fetched from service manager
                // since it will require constructor arguments
                $class = $container->get($session['save_handler']);
            }

            if (!($class instanceof SaveHandlerInterface)) {
                throw new \InvalidArgumentException(
                    'Session save handler must implement Zend\Session\SaveHandler\SaveHandlerInterface.'
                );
            }

            $sessionSaveHandler = $class;
        }

        $sessionManager = new SessionManager(
            $sessionConfig,
            $sessionStorage,
            $sessionSaveHandler,
            isset($session['validators'])
                ? $session['validators']
                : []
        );

        Container::setDefaultManager($sessionManager);
        return $sessionManager;
    }
}
