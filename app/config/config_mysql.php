<?php

define('DB_USER', $_SERVER['HTTP_HOST'] != 'localhost' ?
    "jodu15" :
    "root");

define('DB_PASSWORD', $_SERVER['HTTP_HOST'] != 'localhost' ?
    "1+KC2eH." :
    "");

define('DB_DSN', $_SERVER['HTTP_HOST'] != 'localhost' ?
    "mysql:host=blu-ray.student.bth.se;dbname=jodu15" :
    "mysql:host=localhost;dbname=jodu15");

return [
    'dsn'            => DB_DSN,
    'username'       => DB_USER,
    'password'       => DB_PASSWORD,
    'driver_options' => [PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'"],

    //'verbose' => true, 
    //'debug_connect' => 'true', 
];
