<?php

namespace roaresearch\composer\utils;

use Composer\{Factory, Script\Event};
use InvalidArgumentException;

class ComposerHelper
{
    /**
     * @param  Event  $event
     */
    public static function init(Event $event)
    {
        $composer = $event->getComposer();
        if (version_compare($composer::VERSION, '1.3.0', 'le')) {
            $event->stopPropagation();
            echo "Please update your composer version to 1.3 or higher.\n";
            exit(1);
        }
    }

    /**
     * Render a TPL template and saves it into a file.
     *
     * @param string $location relative to the composer project root
     * @param TPL $tpl
     * @return bool
     */
    public static function renderFile(string $location, TPL $tpl): bool
    {
        return false !== file_put_contents(
            static::parseLocation($location),
            $tpl->render()
        );
    }

    /**
     * @return string the full file path.
     * @throws InvalidArgumentException when the string contains '..'
     */
    protected static function parseLocation(string $location): string
    {
        static $projectDir = null;
        $projectDir ??= dirname(Factory::getComposerFile());

        if (str_contains($location, '..')) {
            throw new InvalidArgumentException(
                'File route must not contain `..`.'
            );
        }

        return $projectDir . DIRECTORY_SEPARATOR
            . strtr($location, '/', DIRECTORY_SEPARATOR);
    }
}
