<?php

include dirname(__DIR__) . '/vendor/autoload.php';

$app = new Silex\Application();

$app->register(new MongoMinify\Silex\ServiceProvider(), array(
    'mongo.server' => 'mongodb://127.0.0.1:27017/ci',
));

$app['debug'] = true;
$app->get('/test/{id}', 'CI\UI\Controller\TestController::view');
$app->get('/hook/github/{id}', 'CI\UI\Controller\HookController::github');

$app->run();