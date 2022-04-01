<?php

namespace roaresearch\composer\utils;

class Yii2DBTPL implements DBTPL
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
            return [
                'dsn' => '{$this->dsn};dbname={$this->name}',
                'username' => '{$this->user}',
                'password' => '{$this->pass}',
            ];
            PHP;
    }
}
