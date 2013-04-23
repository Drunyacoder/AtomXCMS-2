<?php




class Singletone
{
    private static $instance = NULL;

    public static function getInstance()
    {
        if (self::$instance == NULL) {
             self::$instance = new self();
        }
        return self::$instance;
    }


    private function __construct()
    {
    }

    
    private function __clone()
    {
    }
}