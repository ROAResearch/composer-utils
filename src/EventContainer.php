<?php

namespace roaresearch\composer\utils;

use Composer\Script\Event;

class EventContainer
{
    protected readonly array $parsedArguments;
    protected readonly array $packageConfig;

    public function __construct(protected readonly Event $event) {
        $parsed = [];
        foreach ($event->getArguments() as $arg) {
            $parse = explode('=', $arg);
            $parsed[$parse[0]] = $parse[1] ?? true;
        }

        $this->parsedArguments = $parsed;
        $this->packageConfig = $event->getComposer()->getPackage()
            ->getExtra()['utilConfig'] ?? [];
    }

    public function getArg(string $arg, ?string $default = null): ?string
    {
        return $this->parsedArguments[$arg]
            ?? $this->packageConfig[$arg]
            ?? $default;
    }

    public function getArgs(string $arg, ?string $default = null): ?array
    {
        return $this->packageConfig[$arg] ?? $default;
    }
}
