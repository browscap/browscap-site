<?php

declare(strict_types=1);

namespace BrowscapSite\ConfigProvider;

use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Slim\CallableResolver;
use Slim\Handlers\Error;
use Slim\Handlers\NotAllowed;
use Slim\Handlers\NotFound;
use Slim\Handlers\PhpError;
use Slim\Handlers\Strategies\RequestResponse;
use Slim\Http\Environment;
use Slim\Http\Headers;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\Uri;
use Slim\Router;
use Slim\Views\Twig;
use Slim\Views\TwigExtension;

final class SlimDependencies
{
    /** @return mixed[] */
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                'factories' => [
                    'settings' => static function (): array {
                        return [
                            'httpVersion' => '1.1',
                            'responseChunkSize' => 4096,
                            'outputBuffering' => 'append',
                            'determineRouteBeforeAppMiddleware' => false,
                            'displayErrorDetails' => false,
                            'addContentLengthHeader' => true,
                            'routerCacheFile' => false,
                            'renderer' => [
                                'template_path' => __DIR__ . '/../templates/',
                            ],
                            'logger' => [
                                'name' => 'slim-app',
                                'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/app.log',
                                'level' => Logger::DEBUG,
                            ],
                        ];
                    },
                    'environment' => static function (): Environment {
                        return new Environment($_SERVER);
                    },
                    'request' => static function (ContainerInterface $container) {
                        return Request::createFromEnvironment($container->get('environment'));
                    },
                    'response' => static function (ContainerInterface $container) {
                        $headers = new Headers(['Content-Type' => 'text/html; charset=UTF-8']);

                        return (new Response(200, $headers))
                            ->withProtocolVersion($container->get('settings')['httpVersion']);
                    },
                    'router' => static function (ContainerInterface $container) {
                        $routerCacheFile = false;
                        if (isset($container->get('settings')['routerCacheFile'])) {
                            $routerCacheFile = $container->get('settings')['routerCacheFile'];
                        }

                        $router = (new Router())->setCacheFile($routerCacheFile);
                        $router->setContainer($container);

                        return $router;
                    },
                    'foundHandler' => static function () {
                        return new RequestResponse();
                    },
                    'phpErrorHandler' => static function (ContainerInterface $container) {
                        return new PhpError($container->get('settings')['displayErrorDetails']);
                    },
                    'errorHandler' => static function (ContainerInterface $container) {
                        return new Error(
                            $container->get('settings')['displayErrorDetails']
                        );
                    },
                    'notFoundHandler' => static function () {
                        return new NotFound();
                    },
                    'notAllowedHandler' => static function () {
                        return new NotAllowed();
                    },
                    'callableResolver' => static function (ContainerInterface $container) {
                        return new CallableResolver($container);
                    },
                    Twig::class => static function (ContainerInterface $container) {
                        $view = new Twig(__DIR__ . '/../../views', []);

                        $view->addExtension(new TwigExtension(
                            $container->get('router'),
                            Uri::createFromEnvironment($container->get('environment'))
                        ));

                        return $view;
                    },
                ],
            ],
        ];
    }
}
