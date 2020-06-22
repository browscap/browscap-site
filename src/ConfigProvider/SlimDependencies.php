<?php

declare(strict_types=1);

namespace BrowscapSite\ConfigProvider;

use Psr\Container\ContainerInterface;
use Slim\Views\Twig;

final class SlimDependencies
{
    /** @return mixed[] */
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                'factories' => [
                    Twig::class => static function (ContainerInterface $container) {
                        return Twig::create(__DIR__ . '/../../views');
                    },
                ],
            ],
        ];
    }
}
