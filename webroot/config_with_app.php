<?php
/**
 * Config file for pagecontrollers, creating an instance of $app.
 *
 */

// Get environment & autoloader.
require __DIR__ . '/config.php';

// Create services and inject into the app.
$di  = new \Anax\DI\CDIFactoryDefault();

$di->setShared('db', function () {
    $db = new \Mos\Database\CDatabaseBasic();
    $db->setOptions(require ANAX_APP_PATH . 'config/config_mysql.php');
    $db->connect();
    return $db;
});

$di->set('CommentController', function () use ($di) {
    $controller = new \Anax\Comment\CommentController();
    $controller->setDI($di);
    return $controller;
});

$di->set('UsersController', function () use ($di) {
    $controller = new \Anax\Users\UsersController();
    $controller->setDI($di);
    return $controller;
});

$app = new \Anax\MVC\CApplicationBasic($di);

function prettydump($array, $die = false) //Custom dump function
{
    if ($die) {
        die("<pre>" . htmlentities(print_r($array, 1)) . "</pre>");
    } else {
        echo "<pre>" . htmlentities(print_r($array, 1)) . "</pre>";
    }
}
