<?php

namespace SessionMiddleware;

use Zend\Session;
use SessionMiddleware;

/**
 * Class ConfigProvider
 *
 * @package SessionMiddleware
 * @author Daniel Wendrich <daniel.wendrich@gmail.com>
 */
class ConfigProvider
{
    public function __invoke()
    {
        return [
            'dependencies' => $this->getDependencies(),
            'session' => $this->getDefaultSessionConfig(),
        ];
    }

    public function getDefaultSessionConfig()
    {
        return [
            'config' => [
                'class' => Session\Config\SessionConfig::class,
                'options' => [
                    'name' => 'my_app',
                ],
            ],
            'storage' => Session\Storage\SessionArrayStorage::class,
        ];
    }

    public function getDependencies()
    {
        return [
            'invokables' => [
            ],
            'factories'  => [
                Middleware\SessionMiddleware::class => Factory\SessionMiddlewareFactory::class,
                Session\SessionManager::class => Factory\SessionManagerFactory::class,
            ],
            'abstract_factories' => [
            ]
        ];
    }
}
