<?php

namespace roaresearch\composer\utils;

use Composer\Script\Event;

class EventContainer
{
    protected const DEV_ENVIRONMENT = 'dev';
    protected const PROD_ENVIRONMENT = 'prod';
    protected const ENVIRONMENT_ARG = 'environment';

    protected readonly array $parsedArguments;
    protected readonly array $packageConfig;

    public function __construct(protected readonly Event $event) {
        $parsed = [];
        foreach ($event->getArguments() as $arg) {
            $parse = explode('=', $arg, 2);
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
            ?? $this->getEnv($arg)
            ?? $default;
    }

    public function getArgs(string $arg, ?string $default = null): ?array
    {
        return $this->packageConfig[$arg] ?? $default;
    }
    
    public function getEnv(string $arg): ?string
    {
        return (false === $val = getenv($arg))? null : $val;
    }

    public function getIsProd(): bool
    {
        return $this->getEnv(static::ENVIRONMENT_ARG) == static::PROD_ENVIRONMENT;
    }
}
