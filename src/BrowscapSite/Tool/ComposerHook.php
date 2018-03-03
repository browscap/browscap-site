<?php
declare(strict_types=1);

namespace BrowscapSite\Tool;

use Composer\Script\Event;

final class ComposerHook
{
    private const BUILD_DIRECTORY = __DIR__ . '/../../../vendor/build';
    private const RESOURCE_DIRECTORY = __DIR__ . '/../../../vendor/browscap/browscap/resources/';

    /**
     * @param Event $event
     * @throws \OutOfBoundsException
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \BrowscapPHP\Exception
     * @throws \Exception
     * @throws \Assert\AssertionFailedException
     */
    public static function postInstall(Event $event): void
    {
        self::postUpdate($event);
    }

    /**
     * @param Event $event
     * @throws \OutOfBoundsException
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \BrowscapPHP\Exception
     * @throws \Exception
     * @throws \Assert\AssertionFailedException
     */
    public static function postUpdate(Event $event): void
    {
        (new UpdateBrowscapVersionUsed(self::BUILD_DIRECTORY, self::RESOURCE_DIRECTORY))->__invoke($event->getIO());
    }
}
