<?php
declare(strict_types=1);

namespace BrowscapSite\Tool;

use BrowscapSite\BuildGenerator\UpdateBrowscapVersionUsedFactory;
use Composer\Script\Event;

final class ComposerHook
{
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
        (new UpdateBrowscapVersionUsedFactory())->__invoke()->__invoke($event->getIO());
    }
}
