<?php

namespace roaresearch\composer\utils;

use const STDIN, STDOUT, STDERR, PHP_EOL;

class ConsoleHelper
{
    /**
     * Gets input from STDIN and returns a string right-trimmed for EOLs.
     *
     * @param bool $raw If set to true, returns the raw string without trimming
     * @return string the string read from stdin
     */
    public static function stdin(bool $raw = false): string
    {
        return $raw ? fgets(STDIN) : rtrim(fgets(STDIN), PHP_EOL);
    }

    /**
     * Prints a string to STDOUT.
     *
     * @param string $string the string to print
     * @return int|false Number of bytes printed or false on error
     */
    public static function stdout(string $string): int|false
    {
        return fwrite(STDOUT, $string . PHP_EOL);
    }

    /**
     * Prints a string to STDERR.
     *
     * @param string $string the string to print
     * @return int|false Number of bytes printed or false on error
     */
    public static function stderr(string $string): int|false
    {
        return fwrite(STDERR, $string);
    }

    /**
     * Asks the user for input. Ends when the user types a carriage return (PHP_EOL). Optionally, It also provides a
         * prompt.
     *
     * @param ?string $prompt the prompt to display before waiting for input (optional)
     * @return string the user's input
     */
    public static function input(
        ?string $prompt = null,
        ?string $current = null
    ): string {
        if ($current) {
            $prompt .= " ('$current')";
        }
        if (isset($prompt)) {
            static::stdout($prompt);
        }

        return static::stdin() ?: $current ?: '';
    }

    /**
     * Prints text to STDOUT appended with a carriage return (PHP_EOL).
     *
     * @param ?string $string the text to print
     * @return int|false number of bytes printed or false on error.
     */
    public static function output(?string $string = null): int|false
    {
        return static::stdout($string . PHP_EOL);
    }

    /**
     * Prints text to STDERR appended with a carriage return (PHP_EOL).
     *
     * @param ?string $string the text to print
     * @return int|false number of bytes printed or false on error.
     */
    public static function error(?string $string = null): int|false
    {
        return static::stderr($string . PHP_EOL);
    }
}
