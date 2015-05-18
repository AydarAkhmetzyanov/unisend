<?php

ini_set('display_startup_errors',1);
ini_set('display_errors',1);
error_reporting(E_ALL);

define('DS', DIRECTORY_SEPARATOR);

define('DB_HOST','cdfservices.cloudapp.net');
define('DB_USER','test');
define('DB_PASS','test');
define('DB_NAME','test');

$connectors=array('b24');

class Database
{

    private static $instance = NULL;
    private function __construct() {}
    private function __clone(){}
    public static function getInstance() {
        if (!self::$instance){
            try {
                self::$instance = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS,
                  array(
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8; SET time_zone = '+3:00';"
                  ));
            } catch ( PDOException $e ) {
                print( "Error connecting to SQL Server." );
                die(print_r($e));
            }
        }
        return self::$instance;
    }

}
