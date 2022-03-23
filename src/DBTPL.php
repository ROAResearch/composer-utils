<?php

namespace roaresearch\composer\utils;

interface DBTPL extends TPL
{
    public function __construct(
        string $user,
        string $pass,
        string $dsn,
        string $name
    );
}
