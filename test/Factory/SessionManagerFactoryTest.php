<?php

namespace SessionMiddlewareTest\Factory;

use PHPUnit\Framework\TestCase;
use SessionMiddleware\Factory\SessionManagerFactory;
use Zend\ServiceManager\ServiceManager;
use Zend\Session;

class SessionManagerFactoryTest extends TestCase
{
    public function testCreateInstanceWithoutConfig()
    {
        $config = [];
        $container = $this->prophesize(ServiceManager::class);
        $container->get('config')->willReturn($config);

        $factory = new SessionManagerFactory();
        $sessionManager = $factory->__invoke($container->reveal(), 'foo');

        $this->assertInstanceOf(Session\SessionManager::class, $sessionManager);
    }

    public function testCreateInstanceWithConfig()
    {
        $config = [
            'session' => [
                'config' => [
                    'class' => Session\Config\SessionConfig::class,
                    'options' => [
                        'name' => 'my_app',
                        'cookie_httponly' => true,
                        'use_cookies' => true,
                        'cookie_lifetime' => 7200,
                    ],
                ],
                'storage' => Session\Storage\SessionArrayStorage::class,
                'validators' => [
                    Session\Validator\RemoteAddr::class,
                    Session\Validator\HttpUserAgent::class,
                ]
            ]
        ];

        $container = $this->prophesize(ServiceManager::class);
        $container->get('config')->willReturn($config);

        $factory = new SessionManagerFactory();

        /** @var Session\SessionManager $sessionManager */
        $sessionManager = $factory->__invoke($container->reveal(), 'foo');

        $this->assertInstanceOf(Session\SessionManager::class, $sessionManager);
        $this->assertInstanceOf(Session\Config\SessionConfig::class, $sessionManager->getConfig());
        $this->assertInstanceOf(Session\Storage\SessionArrayStorage::class, $sessionManager->getStorage());

        $this->assertEquals(1, $sessionManager->getConfig()->getCookieHttpOnly());
        $this->assertEquals(1, $sessionManager->getConfig()->getUseCookies());
        $this->assertEquals(7200, $sessionManager->getConfig()->getCookieLifetime());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Session config must implement Zend\Session\Config\ConfigInterface.
     */
    public function testCreateInstanceWithNotSessionConfigInterfaceThrowsException()
    {
        $config = [
            'session' => [
                'config' => [
                    'class' => \stdClass::class,
                ],
            ]
        ];

        $container = $this->prophesize(ServiceManager::class);
        $container->get('config')->willReturn($config);

        $factory = new SessionManagerFactory();

        /** @var Session\SessionManager $sessionManager */
        $sessionManager = $factory->__invoke($container->reveal(), 'foo');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Session storage must implement Zend\Session\Storage\StorageInterface.
     */
    public function testCreateInstanceWithNotSessionStorageInterfaceThrowsException()
    {
        $config = [
            'session' => [
                'config' => [
                    'class' => Session\Config\SessionConfig::class,
                ],
                'storage' => \stdClass::class,
            ],
        ];

        $container = $this->prophesize(ServiceManager::class);
        $container->get('config')->willReturn($config);

        $factory = new SessionManagerFactory();

        /** @var Session\SessionManager $sessionManager */
        $sessionManager = $factory->__invoke($container->reveal(), 'foo');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Session save handler must implement Zend\Session\SaveHandler\SaveHandlerInterface.
     */
    public function testCreateInstanceWithNotSessionSaveHandlerInterfaceThrowsException()
    {
        $config = [
            'session' => [
                'config' => [
                    'class' => Session\Config\SessionConfig::class,
                ],
                'storage' => Session\Storage\SessionArrayStorage::class,
                'save_handler' => new \stdClass(),
            ],
        ];

        $container = $this->prophesize(ServiceManager::class);
        $container->get('config')->willReturn($config);

        $factory = new SessionManagerFactory();

        /** @var Session\SessionManager $sessionManager */
        $sessionManager = $factory->__invoke($container->reveal(), 'foo');
    }

    public function testCreateInstanceWithSessionSaveHandlerFetchedByServiceManager()
    {
        $config = [
            'session' => [
                'config' => [
                    'class' => Session\Config\SessionConfig::class,
                ],
                'storage' => Session\Storage\SessionArrayStorage::class,
                'save_handler' => 'Application\Test\SessionSaveHandler',
            ],
        ];

        $container = $this->prophesize(ServiceManager::class);
        $container->get('config')->willReturn($config);

        $saveHandlerInstance = $this->prophesize(Session\SaveHandler\SaveHandlerInterface::class);
        $container->get('Application\Test\SessionSaveHandler')->willReturn(
            $saveHandlerInstance->reveal()
        );

        $factory = new SessionManagerFactory();

        /** @var Session\SessionManager $sessionManager */
        $sessionManager = $factory->__invoke($container->reveal(), 'foo');

        $this->assertInstanceOf(
            Session\SaveHandler\SaveHandlerInterface::class,
            $sessionManager->getSaveHandler()
        );
    }
}