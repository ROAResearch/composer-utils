<?php

namespace roaresearch\composer\utils;

class SimpleDBTPL implements DBTPL
{
    public function __construct(
        public readonly string $user,
        public readonly string $pass,
        public readonly string $dsn,
        public readonly string $name
    ) {
    }

    public function render(): string
    {
        return <<<PHP
            <?php
            \$dbuser = '{$this->user}';
            \$dbpass = '{$this->pass}';
            \$dbdsn = '{$this->dsn}';
            \$dbname = '{$this->name}';
            PHP;
    }
}
