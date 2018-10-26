<?php

declare(strict_types=1);

namespace BrowscapSite\ConfigProvider;

use BrowscapSite\Handler\DownloadHandler;
use BrowscapSite\Handler\PsrRequestHandlerWrapper;
use BrowscapSite\Metadata\Metadata;
use BrowscapSite\Renderer\Renderer;
use BrowscapSite\Renderer\TwigRenderer;
use BrowscapSite\Tool\RateLimiter;
use PDO;
use Psr\Container\ContainerInterface;
use Slim\Http\Response;
use Slim\Views\Twig;
use Zend\ConfigAggregator\ConfigAggregator;

final class AppConfig
{
    private const BAN_CONFIGURATION = 'banConfiguration';
    private const BROWSCAP_FILES_LIST = 'browscapFilesList';

    public function __invoke(): array
    {
        return [
            'dependencies' => $this->dependencies(),
            'rateLimiter' => [
                'rateLimitDownloads' => 30, // How many downloads per $rateLimitPeriod
                'rateLimitPeriod' => 1, // Download limit period in HOURS
                'tempBanPeriod' => 3, // Tempban period in DAYS
                'tempBanLimit' => 5, // How many tempbans allowed in $tempBanPeriod before permaban
            ],
            'db' => [
                'dsn' => 'mysql:dbname=browscap',
                'user' => '',
                'pass' => '',
            ],
            'debug' => false,
            ConfigAggregator::ENABLE_CACHE => true,
        ];
    }

    private function dependencies(): array
    {
        return [
            'factories' => [
                PDO::class => function (ContainerInterface $container): PDO {
                    $dbConfig = $container->get('Config')['db'];
                    return new PDO($dbConfig['dsn'], $dbConfig['user'], $dbConfig['pass']);
                },
                self::BAN_CONFIGURATION => function (ContainerInterface $container): array {
                    $banConfiguration = $container->get('Config')['rateLimiter'];
                    if (!$banConfiguration) {
                        throw new \RuntimeException('Rate limit configuration not set');
                    }
                    return $banConfiguration;
                },
                RateLimiter::class => function (ContainerInterface $container): RateLimiter {
                    return new RateLimiter($container->get(PDO::class), $container->get(self::BAN_CONFIGURATION));
                },
                Metadata::class => function (): Metadata {
                    return Metadata::fromArray(require __DIR__ . '/../../vendor/build/metadata.php');
                },
                DownloadHandler::class => function (ContainerInterface $container) {
                    return new PsrRequestHandlerWrapper(new DownloadHandler(
                        $container->get(Renderer::class),
                        $container->get(Metadata::class),
                        $container->get(self::BROWSCAP_FILES_LIST),
                        $container->get(self::BAN_CONFIGURATION)
                    ));
                },
                Renderer::class => function (ContainerInterface $container): Renderer {
                    return new TwigRenderer(
                        $container->get(Twig::class),
                        new Response(200)
                    );
                },
                self::BROWSCAP_FILES_LIST => function (): array {
                    return [
                        'asp' => [
                            'BrowsCapINI' => [
                                'name' => 'browscap.ini',
                                'size' => null,
                                'description' => 'This is the standard version of browscap.ini file for IIS 5.x and greater.',
                            ],
                            'Full_BrowsCapINI' => [
                                'name' => 'full_asp_browscap.ini',
                                'size' => null,
                                'description' => 'This is a larger version of browscap.ini with all the new properties.',
                            ],
                            'Lite_BrowsCapINI' => [
                                'name' => 'lite_asp_browscap.ini',
                                'size' => null,
                                'description' => 'This is a smaller version of browscap.ini file containing major browsers & search engines. This file is adequate for most websites.',
                            ],
                        ],
                        'php' => [
                            'PHP_BrowsCapINI' => [
                                'name' => 'php_browscap.ini',
                                'size' => null,
                                'description' => 'This is a special version of browscap.ini for PHP users only!',
                            ],
                            'Full_PHP_BrowsCapINI' => [
                                'name' => 'full_php_browscap.ini',
                                'size' => null,
                                'description' => 'This is a larger version of php_browscap.ini with all the new properties.',
                            ],
                            'Lite_PHP_BrowsCapINI' => [
                                'name' => 'lite_php_browscap.ini',
                                'size' => null,
                                'description' => 'This is a smaller version of php_browscap.ini file containing major browsers & search engines. This file is adequate for most websites.',
                            ],
                        ],
                        'other' => [
                            'BrowsCapXML' => [
                                'name' => 'browscap.xml',
                                'size' => null,
                                'description' => 'This is the standard version of browscap.ini file in XML format.',
                            ],
                            'BrowsCapCSV' => [
                                'name' => 'browscap.csv',
                                'size' => null,
                                'description' => 'This is an industry-standard comma-separated-values version of browscap.ini. Easily imported into Access, Excel, MySQL & others.',
                            ],
                            'BrowsCapJSON' => [
                                'name' => 'browscap.json',
                                'size' => null,
                                'description' => 'This is a JSON (JavaScript Object Notation) version of browscap.ini. This is usually used with JavaScript.',
                            ],
                            'BrowsCapZIP' => [
                                'name' => 'browscap.zip',
                                'size' => null,
                                'description' => 'This archive combines all the above files into one download that is smaller than all eight files put together.',
                            ],
                        ],
                    ];
                },
            ],
        ];
    }
}
