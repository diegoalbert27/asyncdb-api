<?php

namespace App\Libraries;

/**
 * Mssql class
 */
class Database extends \PDO
{

    private static $_instance;

    /**
     * __construct function
     */
    private function __construct()
    {
        $dsn = 'sqlsrv:Server=LAPTOP-4JSVLNQ4;Database=Sventas';

        try {
            parent::__construct($dsn, 'DEVELOPER', 'mavesa22');
        } catch (\PDOException $e) {
            die("DataBase Error: Database failed.<br>{$e->getMessage()}");
        }
    }

    /**
     * getInstance function
     *
     * @return Database
     */
    public static function getInstance()
    {
        if (!isset(self::$_instance)) {
            self::$_instance = new self();
            self::$_instance->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            self::$_instance->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
        }

        return self::$_instance;
    }
}
