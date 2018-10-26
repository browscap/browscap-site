<?php
declare(strict_types=1);

namespace BrowscapSite\ConfigProvider;

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
use Slim\Router;

final class SlimDependencies
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                'factories' => [
                    'settings' => function (): array {
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
                                'level' => \Monolog\Logger::DEBUG,
                            ],
                        ];
                    },
                    'environment' => function (): Environment {
                        return new Environment($_SERVER);
                    },
                    'request' => function (ContainerInterface $container) {
                        return Request::createFromEnvironment($container->get('environment'));
                    },
                    'response' => function (ContainerInterface $container) {
                        $headers = new Headers(['Content-Type' => 'text/html; charset=UTF-8']);
                        $response = new Response(200, $headers);

                        return $response->withProtocolVersion($container->get('settings')['httpVersion']);
                    },
                    'router' => function (ContainerInterface $container) {
                        $routerCacheFile = false;
                        if (isset($container->get('settings')['routerCacheFile'])) {
                            $routerCacheFile = $container->get('settings')['routerCacheFile'];
                        }

                        $router = (new Router())->setCacheFile($routerCacheFile);
                        if (method_exists($router, 'setContainer')) {
                            $router->setContainer($container);
                        }

                        return $router;
                    },
                    'foundHandler' => function () {
                        return new RequestResponse();
                    },
                    'phpErrorHandler' => function (ContainerInterface $container) {
                        return new PhpError($container->get('settings')['displayErrorDetails']);
                    },
                    'errorHandler' => function (ContainerInterface $container) {
                        return new Error(
                            $container->get('settings')['displayErrorDetails']
                        );
                    },
                    'notFoundHandler' => function () {
                        return new NotFound();
                    },
                    'notAllowedHandler' => function () {
                        return new NotAllowed();
                    },
                    'callableResolver' => function (ContainerInterface $container) {
                        return new CallableResolver($container);
                    },
                ],
            ],
        ];
    }
}
