<?php

namespace roaresearch\composer\utils;

use Composer\{Factory, Script\Event};

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

    public static function renderFile(string $location, TPL $tpl): bool
    {
        static $projectDir = null;
        $projectDir ??= dirname(Factory::getComposerFile());

        return false !== file_put_contents(
            $projectDir . DIRECTORY_SEPARATOR
                . strtr($location, '/', DIRECTORY_SEPARATOR),
            $tpl->render()
        );
    }
}
