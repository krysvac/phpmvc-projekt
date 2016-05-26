<?php
/**
 * This is a Anax pagecontroller.
 *
 */

// Get environment & autoloader and the $app-object.
require __DIR__ . '/config_with_app.php';

$app->url->setUrlType(\Anax\Url\CUrl::URL_CLEAN);
$app->theme->configure(ANAX_APP_PATH . 'config/theme_grid.php');

$user = new \Anax\Users\User();
$app->navbar->configure(ANAX_APP_PATH . 'config/navbar_me.php');

$app->router->add('', function () use ($app) {
    $app->theme->setTitle("Hem");
    
    $app->dispatcher->forward([
        'controller' => 'comment',
        'action'     => 'viewNewest',
        'params' => ["question", 4, "triptych-1"],
    ]);
    
    $app->dispatcher->forward([
        'controller' => 'users',
        'action'     => 'viewMostReputation',
        'params' => [4, "triptych-2"],
    ]);
    
    $app->dispatcher->forward([
        'controller' => 'comment',
        'action'     => 'viewMostCommonTags',
        'params' => [4, "triptych-3"],
    ]);
        $app->views->add('default/article', [
        'content' => $app->fileContent->get('front/front.html'),
        ], "flash");
});

$app->router->add('questions', function () use ($app) {
    $app->theme->setTitle("Alla frågor"); 
    $app->dispatcher->forward([
        'controller' => 'comment',
        'action'     => 'view',
        ]);
 
});

$app->router->add('tags', function () use ($app) {
    $app->theme->setTitle("Alla taggar");
    $app->dispatcher->forward([
        'controller' => 'comment',
        'action'     => 'viewTags',
    ]);
});

$app->router->add('users', function () use ($app) {
    $app->theme->setTitle("Alla användare");
    $app->dispatcher->forward([
        'controller' => 'users',
        'action'     => 'list',
        ]);
});

$app->router->add('about', function () use ($app) {
    $app->theme->setTitle("Om sidan");
    $content = $app->fileContent->get('about.md');
    $content = $app->textFilter->doFilter($content, 'shortcode, markdown');
    $app->views->add('default/article', [
        'content' => $content,
    ], "main");
});

$app->router->add('setup', function () use ($app) {
    $app->theme->setTitle("Restore database");
    $app->dispatcher->forward([
        'controller' => 'users',
        'action'     => 'setup',
        ]);
});

$app->router->handle();
$app->theme->render();
