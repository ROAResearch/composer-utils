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

    protected const DEFAULT_USER = 'root';
    protected const DEFAULT_PASS = 'root';
    protected const DEFAULT_DSN = 'mysql:host=127.0.0.1';

    protected const INCORRECT_USER_PASS_MSG = 'DB config incorrect.';
    protected const NOT_ALLOWED_DB_NAME_MSG = 'Not allowed to access database ';
    protected const FILE_GENERATION_SUCCESS = 'Config file generated successfully';
    protected const FILE_GENERATION_FAILURE = 'Failed at generating config file';

    /**
     * It is used to write the user credentials in a PHP file.
     * @see self::dbFile()
     * @param Event $event
     */
    public static function config(Event $event)
    {
        $container = new EventContainer($event);

        $user = $container->getArg(static::USER_ARG, static::DEFAULT_USER);
        $pass = $container->getArg(static::PASS_ARG, static::DEFAULT_PASS);
        $dsn = $container->getArg(static::DSN_ARG, static::DEFAULT_DSN);

        while (null === ($pdo = static::tryPDO($user, $pass, $dsn))) {
            ConsoleHelper::output(static::INCORRECT_USER_PASS_MSG);
            [$user, $pass, $dsn] = static::requestCredentials($dsn);
        }

        $name = $container->getArg(static::NAME_ARG) ?? static::requestName();
        while (!self::useDB($pdo, $name)) {
            ConsoleHelper::stdout(static::NOT_ALLOWED_DB_NAME_MSG . " `$name`");
            $name = self::requestName();
        }


        foreach ($container->getArgs(static::TPLS_ARG) as $location => $tpl) {
            ConsoleHelper::stdout(
                ComposerHelper::renderFile(
                    $location,
                    new $tpl($user, $pass, $dsn, $name)
                )
                ? ("`$location` ". static::FILE_GENERATION_SUCCESS)
                : ("`$location` ". static::FILE_GENERATION_FAILURE)
            );
        }
    }

    public static function blankConfig(Event $event)
    {
        $container = new EventContainer($event);
        $user = $container->getArg(static::USER_ARG, static::DEFAULT_USER);
        $pass = $container->getArg(static::PASS_ARG, static::DEFAULT_PASS);
        $dsn = $container->getArg(static::DSN_ARG, static::DEFAULT_DSN);
        $name = $container->getArg(static::NAME_ARG) ?? static::requestName();

        foreach ($container->getArgs(static::TPLS_ARG) as $location => $tpl) {
            ConsoleHelper::stdout(
                ComposerHelper::renderFile(
                    $location,
                    new $tpl($user, $pass, $dsn, $name)
                )
                ? ("`$location` ". static::FILE_GENERATION_SUCCESS)
                : ("`$location` ". static::FILE_GENERATION_FAILURE)
            );
        }
        
    }

    /**
     * @param string $dsn
     * @return string[] DB user credentials.
     */
    protected static function requestCredentials(string $dsn)
    {
        $user = ConsoleHelper::input('Database username');
        $pass = ConsoleHelper::input('Database password');
        $dsn = ConsoleHelper::input('Database password', $dsn);

        return [$user, $pass, $dsn];
    }

    /**
     * @return string Database name.
     */
    protected static function requestName()
    {
        return ConsoleHelper::input('Database name');
    }

    /**
     * @param string $user DB user.
     * @param string $pass DB password.
     * @param string $dsn PDO DSN
     * @return ?PDO The connection or `null` on failure.
     */
    protected static function tryPDO(string $user, string $pass, string $dsn): ?PDO
    {
        try {
            $pdo = new PDO($dsn, $user, $pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            return $pdo;
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * @param PDO $pdo Object to stablish connection.
     * @param string $dbname Dabase name.
     * @return bool whether conection was stablished.
     */
    protected static function useDb(PDO $pdo, string $dbname): bool
    {
        try {
            $pdo->query("CREATE DATABASE IF NOT EXISTS $dbname");
            $pdo->query("USE $dbname");

            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * @return string Location of `db.php` file.
     */
    protected static function dbFile()
    {
        return dirname(__DIR__) . '/common/config/db.php';
    }

    /**
     * Drops and create database.
     */
    public static function truncate()
    {
        include self::dbFile();
        $pdo = self::createPDO($dbuser, $dbpass);
        $pdo->query("DROP DATABASE IF EXISTS $dbname");
        $pdo->query("DROP DATABASE IF EXISTS {$dbname}_test");
        $pdo->query("CREATE DATABASE $dbname");
        $pdo->query("CREATE DATABASE {$dbname}_test");
    }
}
