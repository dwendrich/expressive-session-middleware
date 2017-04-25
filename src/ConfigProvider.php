<?php

namespace SessionMiddleware;

use Zend\Session;
use Zend\Session\ConfigProvider as ZendSessionConfigProvider;

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
            'dependencies' => $this->getZendSessionDependencies(),
            'session' => $this->getSessionConfig(),
        ];
    }

    public function getSessionConfig()
    {
        return [
            'config' => [
                'class' => Session\Config\SessionConfig::class,
                'options' => [
                    'name' => 'my_app',
                ],
            ],
            'storage' => Session\Storage\SessionArrayStorage::class,
            'validators' => [
                Session\Validator\RemoteAddr::class,
                Session\Validator\HttpUserAgent::class
            ],
        ];
    }

    public function getZendSessionDependencies()
    {
        return (new ZendSessionConfigProvider())->getDependencyConfig();
    }
}
