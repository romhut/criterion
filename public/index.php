<?php

include dirname(__DIR__) . '/vendor/autoload.php';

$app = new Silex\Application();

$app->register(new MongoMinify\Silex\ServiceProvider(), array(
    'mongo.server' => 'mongodb://127.0.0.1:27017/ci',
));

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => dirname(__DIR__) . '/lib/CI/UI/View',
));

$app['debug'] = true;
$app->get('/', 'CI\UI\Controller\ProjectsController::all');
$app->match('/project/create', 'CI\UI\Controller\ProjectsController::create')->method('POST|GET');
$app->match('/project/{id}', 'CI\UI\Controller\ProjectsController::view')->method('POST|GET');
$app->match('/project/run/{id}', 'CI\UI\Controller\ProjectsController::run')->method('GET');
$app->get('/test/{id}', 'CI\UI\Controller\TestController::view');
$app->get('/test/delete/{id}', 'CI\UI\Controller\TestController::delete');
$app->get('/hook/{id}', 'CI\UI\Controller\HookController::hook');

$app->run();