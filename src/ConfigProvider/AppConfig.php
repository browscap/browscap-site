<?php

declare(strict_types=1);

namespace BrowscapSite\ConfigProvider;

use BrowscapSite\BuildGenerator\BuildGenerator;
use BrowscapSite\BuildGenerator\BuildGeneratorFactory;
use BrowscapSite\Handler\DownloadHandler;
use BrowscapSite\Handler\PsrRequestHandlerWrapper;
use BrowscapSite\Handler\StatsHandler;
use BrowscapSite\Handler\StreamHandler;
use BrowscapSite\Handler\UserAgentLookupHandler;
use BrowscapSite\Handler\VersionHandler;
use BrowscapSite\Handler\VersionNumberHandler;
use BrowscapSite\Handler\VersionXmlHandler;
use BrowscapSite\Metadata\Metadata;
use BrowscapSite\Renderer\Renderer;
use BrowscapSite\Renderer\TwigRenderer;
use BrowscapSite\Tool\AnalyseStatistics;
use BrowscapSite\Tool\DeleteOldDownloadLogs;
use BrowscapSite\Tool\PdoRateLimiter;
use BrowscapSite\Tool\RateLimiter;
use BrowscapSite\UserAgentTool\BrowscapPhpUserAgentTool;
use BrowscapSite\UserAgentTool\UserAgentTool;
use Cache\Adapter\Filesystem\FilesystemCachePool;
use Laminas\ConfigAggregator\ConfigAggregator;
use Laminas\Diactoros\Response\HtmlResponse;
use LazyPDO\LazyPDO as PDO;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Slim\Views\Twig;

use function getenv;

/**
 * @psalm-type FilesListItem = array{
 *   name: string,
 *   size: int|string|null,
 *   description: string
 * }
 * @psalm-type FilesList = array{
 *   asp: array{
 *     BrowsCapINI: FilesListItem,
 *     Full_BrowsCapINI: FilesListItem,
 *     Lite_BrowsCapINI: FilesListItem
 *   },
 *   php: array{
 *     PHP_BrowsCapINI: FilesListItem,
 *     Full_PHP_BrowsCapINI: FilesListItem,
 *     Lite_PHP_BrowsCapINI: FilesListItem
 *   },
 *   other: array{
 *     BrowsCapXML: FilesListItem,
 *     BrowsCapCSV: FilesListItem,
 *     BrowsCapJSON: FilesListItem,
 *     BrowsCapZIP: FilesListItem
 *   }
 * }
 * @psalm-type BanConfiguration = array{
 *   rateLimitDownloads: int,
 *   rateLimitPeriod: int,
 *   tempBanPeriod: int,
 *   tempBanLimit: int
 * }
 */
final class AppConfig
{
    public const DEFAULT_BAN_CONFIGURATION = [
        'rateLimitDownloads' => 30, // How many downloads per $rateLimitPeriod
        'rateLimitPeriod' => 1, // Download limit period in HOURS
        'tempBanPeriod' => 3, // Tempban period in DAYS
        'tempBanLimit' => 5, // How many tempbans allowed in $tempBanPeriod before permaban
    ];

    public const DEFAULT_FILES_LIST = [
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

    private const BAN_CONFIGURATION   = 'banConfiguration';
    private const BROWSCAP_FILES_LIST = 'browscapFilesList';

    /** @return mixed[] */
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->dependencies(),
            'rateLimiter' => self::DEFAULT_BAN_CONFIGURATION,
            'db' => [
                'dsn' => 'mysql:dbname=browscap',
                'user' => '',
                'pass' => '',
            ],
            'debug' => false,
            ConfigAggregator::ENABLE_CACHE => true,
        ];
    }

    /** @return mixed[] */
    private function dependencies(): array
    {
        return [
            'factories' => [
                UserAgentTool::class => static function (ContainerInterface $container): UserAgentTool {
                    return new BrowscapPhpUserAgentTool(
                        new FilesystemCachePool(
                            new Filesystem(
                                new Local(__DIR__ . '/../../cache')
                            )
                        ),
                        $container->get(LoggerInterface::class)
                    );
                },
                LoggerInterface::class => static function (ContainerInterface $container): LoggerInterface {
                    $logLevel = (int) (getenv('BC_BUILD_LOG') ?: Logger::NOTICE);
                    $logger   = new Logger('browscan-site');
                    $logger->pushHandler(new ErrorLogHandler(ErrorLogHandler::OPERATING_SYSTEM, $logLevel));

                    return $logger;
                },
                PDO::class => static function (ContainerInterface $container): PDO {
                    $dbConfig = $container->get('Config')['db'];

                    return new PDO($dbConfig['dsn'], $dbConfig['user'], $dbConfig['pass']);
                },
                self::BAN_CONFIGURATION => static function (ContainerInterface $container): array {
                    $banConfiguration = $container->get('Config')['rateLimiter'];
                    if (! $banConfiguration) {
                        throw new RuntimeException('Rate limit configuration not set');
                    }

                    return $banConfiguration;
                },
                RateLimiter::class => static function (ContainerInterface $container): PdoRateLimiter {
                    return new PdoRateLimiter(
                        $container->get(PDO::class),
                        $container->get(self::BAN_CONFIGURATION)
                    );
                },
                Metadata::class => static function (): Metadata {
                    return Metadata::fromArray(require __DIR__ . '/../../vendor/build/metadata.php');
                },
                DownloadHandler::class => static function (ContainerInterface $container) {
                    return new PsrRequestHandlerWrapper(new DownloadHandler(
                        $container->get(Renderer::class),
                        $container->get(Metadata::class),
                        $container->get(self::BROWSCAP_FILES_LIST),
                        $container->get(self::BAN_CONFIGURATION)
                    ));
                },
                UserAgentLookupHandler::class => static function (ContainerInterface $container) {
                    return new PsrRequestHandlerWrapper(new UserAgentLookupHandler(
                        $container->get(Renderer::class),
                        $container->get(Metadata::class),
                        $container->get(UserAgentTool::class),
                        true
                    ));
                },
                StreamHandler::class => static function (ContainerInterface $container) {
                    return new PsrRequestHandlerWrapper(new StreamHandler(
                        $container->get(RateLimiter::class),
                        $container->get(Metadata::class),
                        $container->get(self::BROWSCAP_FILES_LIST),
                        __DIR__ . '/../../vendor/build'
                    ));
                },
                StatsHandler::class => static function (ContainerInterface $container) {
                    return new PsrRequestHandlerWrapper(new StatsHandler(
                        $container->get(Renderer::class),
                        $container->get(PDO::class)
                    ));
                },
                VersionHandler::class => static function (ContainerInterface $container) {
                    return new PsrRequestHandlerWrapper(new VersionHandler(
                        $container->get(Renderer::class),
                        $container->get(Metadata::class)
                    ));
                },
                VersionNumberHandler::class => static function (ContainerInterface $container) {
                    return new PsrRequestHandlerWrapper(new VersionNumberHandler(
                        $container->get(Renderer::class),
                        $container->get(Metadata::class)
                    ));
                },
                VersionXmlHandler::class => static function (ContainerInterface $container) {
                    return new PsrRequestHandlerWrapper(new VersionXmlHandler(
                        $container->get(Metadata::class),
                        $container->get(self::BROWSCAP_FILES_LIST)
                    ));
                },
                Renderer::class => static function (ContainerInterface $container): Renderer {
                    return new TwigRenderer(
                        $container->get(Twig::class),
                        new HtmlResponse('', 200)
                    );
                },
                BuildGenerator::class => BuildGeneratorFactory::class,
                AnalyseStatistics::class => static function (ContainerInterface $container): AnalyseStatistics {
                    return new AnalyseStatistics($container->get(PDO::class));
                },
                DeleteOldDownloadLogs::class => static function (ContainerInterface $container): DeleteOldDownloadLogs {
                    return new DeleteOldDownloadLogs($container->get(PDO::class));
                },
                self::BROWSCAP_FILES_LIST => static function (): array {
                    return self::DEFAULT_FILES_LIST;
                },
            ],
        ];
    }
}
