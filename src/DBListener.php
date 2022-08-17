<?php

namespace roaresearch\composer\utils;

use Composer\Script\Event;
use PDO;
use PDOException;

class DBListener
{
    protected const USER_ARG = 'dbuser';
    protected const PASS_ARG = 'dbpass';
    protected const DSN_ARG = 'dbdsn';
    protected const NAME_ARG = 'dbname';
    protected const TPLS_ARG = 'dbtpls';
    protected const TEST_SUFFIX_ARG = 'dbtestsuffix';
    protected const TEST_TPLS_ARG = 'dbtesttpls';

    protected const DEFAULT_DSN = 'mysql:host=127.0.0.1';

    protected const USER_PROMPT = 'Database Username';
    protected const PASS_PROMPT = 'Database Password';
    protected const DSN_PROMPT = 'Database DSN';
    protected const NAME_PROMPT = 'Database Name';

    protected const INCORRECT_USER_PASS_MSG = 'DB credentials incorrect';
    protected const NOT_ALLOWED_DB_NAME_MSG = 'Not allowed to access DB ';
    protected const FILE_RENDER_SUCCESS_MSG = 'Rendered successfully.';
    protected const FILE_RENDER_FAILURE_MSG = 'Rendering failed.';

    /**
     * @var EventContainer
     */
    protected static EventContainer $container;
 
    /**
     * @var PDO connection generated with the provided credentials
     */
    protected static PDO $pdo;
 
    /**
     * @var string database user
     */
    protected static string $user;
 
    /**
     * @var string database password
     */
    protected static string $pass;
 
    /**
     * @var string dns to access the databse
     */
    protected static string $dsn;
 
    /**
     * @var string name of the database
     */
    protected static string $name;
 
    /**
     * @var string suffix attached to the name to create the test database
     */
    protected static string $suffix;

    /**
     * Check if the database credentials are valid and write them into files.
     * @param Event $event
     */
    public static function config(Event $event)
    {
        static::fetchArgs($event);

        while (!static::buildPDO()) {
            ConsoleHelper::error(static::INCORRECT_USER_PASS_MSG);
            static::promptCredentials();
        }

        while (!static::useDB()) {
            ConsoleHelper::error(static::NOT_ALLOWED_DB_NAME_MSG . static::$name);
            static::$name = self::requestName();
        }

        static::renderTPLs(static::TPLS_ARG);
        if (static::$suffix) {
            static::$name .= static::$suffix;
            static::renderTPLs(static::TEST_TPLS_ARG);
        }
    }

    /**
     * @param Event $event
     */
    public static function blankConfig(Event $event)
    {
        static::fetchArgs($event);

        static::renderTPLs(static::TPLS_ARG);
        if (static::$suffix) {
            static::$name .= static::$suffix;
            static::renderTPLs(static::TEST_TPLS_ARG);
        }

    }

    /**
     * Fetch the default arguments.
     *
     * @param Event $event
     */
    protected static function fetchArgs(Event $event)
    {
        static::$container = new EventContainer($event);

        static::$user = static::$container->getArg(static::USER_ARG)
            ?? static::promptUser();
        static::$pass = static::$container->getArg(static::PASS_ARG)
            ?? static::promptPass();
        static::$dsn = static::$container->getArg(
            static::DSN_ARG,
            static::DEFAULT_DSN
        );

        static::$name = static::$container->getArg(static::NAME_ARG)
            ?? static::prompttName();
        static::$suffix = static::$container->getIsProd()
            ? ''
            : static::$container->getArg(static::TEST_SUFFIX_ARG, '');
    }

    /**
     * @return string console input
     */
    protected static function promptUser(): string
    {
        return ConsoleHelper::input(static::USER_PROMPT);
    }

    /**
     * @return string console input
     */
    protected static function promptPass(): string
    {
        return ConsoleHelper::input(static::PASS_PROMPT);
    }

    /**
     * @return string console input
     */
    protected static function promptDsn(): string
    {
        return ConsoleHelper::input(static::DSN_PROMPT, static::$dsn);
    }

    /**
     * @return string console input
     */
    protected static function promptName(): string
    {
        return ConsoleHelper::input(static::NAME_PROMPT);
    }

    /**
     * @param string $dsn
     * @return string[] DB user credentials.
     */
    protected static function promptCredentials()
    {
        static::$user = static::promptUser();
        static::$pass = static::promptPass();
        static::$dsn = static::promptDsn();
    }

    /**
     * @return bool whether the PDO connection was created.
     */
    protected static function buildPDO(): bool
    {
        try {
            static::$pdo = new PDO(static::$dsn, static::$user, static::$pass);
            static::$pdo->setAttribute(
                PDO::ATTR_ERRMODE,
                PDO::ERRMODE_EXCEPTION
            );

            return true;
        } catch (PDOException $e) {
            ConsoleHelper::error($e->getMessage());
            return false;
        }
    }

    /**
     * @param PDO $pdo Object to stablish connection.
     * @param string $dbname Dabase name.
     * @return bool whether conection was stablished.
     */
    protected static function useDb(): bool {
        try {
            $name = '`' . static::$name . '`';
            static::$pdo->query("CREATE DATABASE IF NOT EXISTS $name");
            if (static::$suffix) {
                $testname = '`' . static::$name . static::$suffix . '`';
                static::$pdo->query("CREATE DATABASE IF NOT EXISTS $testname");
            }

            static::$pdo->query("USE $name");

            return true;
        } catch (PDOException $e) {
            ConsoleHelper::error($e->getMessage());
            return false;
        }
    }

    /**
     * @param string $arg the argument to get all the templates and locations to
     *   render.
     */
    protected static function renderTPLs(string $arg): void
    {
        foreach (static::$container->getArgs($arg) as $location => $tpl) {
            ConsoleHelper::output(
                ComposerHelper::renderFile(
                    $location,
                    new $tpl(
                        static::$user,
                        static::$pass,
                        static::$dsn,
                        static::$name
                    )
                )
                ? ("`$location` ". static::FILE_RENDER_SUCCESS_MSG)
                : ("`$location` ". static::FILE_RENDER_FAILURE_MSG)
            );
        }
    }
}
