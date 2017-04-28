# expressive-session-middleware
Session handling middleware based on zend-session for use in zend expressive 2.0 applications.

[![Build Status](https://travis-ci.org/dwendrich/expressive-session-middleware.svg?branch=master)](https://travis-ci.org/dwendrich/expressive-session-middleware)
[![Coverage Status](https://img.shields.io/codecov/c/github/dwendrich/expressive-session-middleware.svg?style=flat)](https://codecov.io/gh/dwendrich/expressive-session-middleware)
[![Latest Stable Version](http://img.shields.io/packagist/v/dwendrich/expressive-session-middleware.svg?style=flat)](https://packagist.org/packages/dwendrich/expressive-session-middleware)

## Requirements
* PHP 7.0 or above
* [zendframework/zend-session](https://docs.zendframework.com/zend-session/)

## Installation
Install the latest version with composer. For information on how to get composer or how to use it, please refer to [getcomposer.org](http://getcomposer.org).
```sh
$ composer require dwendrich/expressive-soap-middleware
```

If during installation you are prompted to inject `Zend\Session\ConfigProvider` into your configuration, you can simply
ignore and continue without it. All relevant configuration is part of `SessionMiddleware\ConfigProvider`.

As part of a zend-expressive 2.0 application add `SessionMiddleware\ConfigProvider::class` to `config/config.php`:
```php
$aggregator = new ConfigAggregator([
 
    // enable SessionMiddleware
    SessionMiddleware\ConfigProvider::class,
    
    // ... other stuff goes here 
 
    // Load application config in a pre-defined order in such a way that local settings
    // overwrite global settings. (Loaded as first to last):
    //   - `global.php`
    //   - `*.global.php`
    //   - `local.php`
    //   - `*.local.php`
    new PhpFileProvider('config/autoload/{{,*.}global,{,*.}local}.php'),
 
    // Load development config if it exists
    new PhpFileProvider('config/development.config.php'),
], $cacheConfig['config_cache_path']);
```

There are two ways of integrating the session middleware into your application.

#### 1. Add the middleware to the programmatic middlewarepipeline
You can add the middleware to the file `config/pipeline.php`:
```php
// Register session handling middleware
$app->pipe(SessionMiddleware::class);
 
// Register the routing middleware in the middleware pipeline
$app->pipeRoutingMiddleware();
$app->pipe(ImplicitHeadMiddleware::class);
$app->pipe(ImplicitOptionsMiddleware::class);
$app->pipe(UrlHelperMiddleware::class);
```
Depending on which middleware should get access to the session, you should prepend `SessionMiddleware` in the pipeline.
Commonly before registering the routing middleware is a good way to go.

This way the middleware is invoked on every request to your application. Since session handling may produce some
overhead which isn't always needed there is an alternative.

#### 2. Add the middleware to a specific route
Add a route definition to either `config/routes.php` or a `RouteDelegator` as part of your application:
```php
$app->route(
    '/path-to-my-action',
    [
        SessionMiddleware::class,
        MyApp\Action\MyAction::class
    ],
    ['GET'],
    'path-to-my-action'
);
```
This way session handling is bound to a specific path in your application where it may be needed.

For further information on programmatic pipelines and routing in zend expressive 2.0 please refer to the
[documentation](https://docs.zendframework.com/zend-expressive/cookbook/autowiring-routes-and-pipelines/).

## Basic usage
Once the session middleware is invoked it will start the session and adds the session manager object as attribute to the
current request. Any middleware which processes this request subsequently, can detect that session handling is started
by testing against the request attribute:
```php
/**
 * Process an incoming server request and return a response, optionally delegating
 * to the next middleware component to create the response.
 *
 * @param ServerRequestInterface $request
 * @param DelegateInterface $delegate
 *
 * @return ResponseInterface
 */
public function process(ServerRequestInterface $request, DelegateInterface $delegate)
{
    $sessionManager = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE, false);
    
    if ($sessionManager) {
        // sessionManager is present and can be used
    }
    
    // further request processing goes here...
}
```

### Storing and retrieving session data
Zend session component uses `Container` objects to access and store session data. For information on this concept please
refer to the [documentation](https://docs.zendframework.com/zend-session/container/).

Following is a simple example on how to use a `Container`:
```php
use Zend\Session\Container;
 
$container = Container('my_namespace');
 
// save 'foo' into the `item` key
$container->item = 'foo';
```

In another part of the application you may want to access this data:
```php
use Zend\Session\Container;
 
$container = Container('my_namespace');
 
// read the content from the `item` key
$foo = $container->item;
```

## Configuration
The session can be configured by adding a `session.global.php` to your `config/autoload` path, for example. You can
use `session.global.php.dist` file (see [session.global.php.dist](config/session.global.php.dist)) as template.
```php
return [
    'session' => [
        'config' => [
            'options' => [
                'name' => 'my_special_session_name',
                'use_cookies' => true,
                'cookie_secure' => false,
            ],
        ],
    ],
];
```
For possible configuration options please refer to the documentation of
[zend-session](https://docs.zendframework.com/zend-session/config/#standard-config) component.

You can override the session configuration instance with any instance or class implementing
`Zend\Session\Config\ConfigInterface`. Simply specify it in session configuration:
```php
return [
    'session' => [
        'class' => Zend\Session\Config\StandardConfig::class,
        'options' => [
            'name' => 'my_app',
        ],
    ],
];
```

For using a certain session storage adapter you can override it in the config, as well. Therefore it has to implement
`Zend\Session\Storage\StorageInterface`:
```php
return [
    'session' => [
        'storage' => new MyApp\Session\StorageAdapter::class,
    ],
];
```

To add validators to the session manager, you cann define them in the config, too, for example:
```php
return [
    'session' => [
        'validators' => [
            Session\Validator\RemoteAddr::class,
            Session\Validator\HttpUserAgent::class,
        ],
    ],
];
```