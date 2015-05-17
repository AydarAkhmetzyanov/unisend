<?php

ini_set('display_startup_errors',1);
ini_set('display_errors',1);
error_reporting(E_ALL);

define('DS', DIRECTORY_SEPARATOR);

define('DB_HOST','cdfservices.cloudapp.net');
define('DB_USER','gentlz');
define('DB_PASS','gentlzpsw');
define('DB_NAME','gentlz');

$connectors=array('b24');

class Database
{

    private static $instance = NULL;
    private function __construct() {}
    private function __clone(){}
    public static function getInstance() {
        if (!self::$instance){
            try {
                self::$instance = new PDO("mysql:host=cdfservices.cloudapp.net;dbname=gentlz", 'gentlz', 'gentlzpsw',
                  array(
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
                  ));
            } catch ( PDOException $e ) {
                print( "Error connecting to SQL Server." );
                die(print_r($e));
            }
        }
        return self::$instance;
    }

}
