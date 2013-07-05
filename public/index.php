<?php

include dirname(__DIR__) . '/vendor/autoload.php';

$app = new Silex\Application();

$app->register(new MongoMinify\Silex\ServiceProvider(), array(
    'mongo.server' => 'mongodb://127.0.0.1:27017/criterion',
));

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => dirname(__DIR__) . '/app/Criterion/UI/View',
));

$app['debug'] = true;
$app->get('/', 'Criterion\UI\Controller\ProjectsController::all');
$app->match('/project/create', 'Criterion\UI\Controller\ProjectsController::create')->method('POST|GET');
$app->match('/project/{id}', 'Criterion\UI\Controller\ProjectsController::view')->method('POST|GET');
$app->match('/project/run/{id}', 'Criterion\UI\Controller\ProjectsController::run')->method('GET');
$app->get('/test/{id}', 'Criterion\UI\Controller\TestController::view');
$app->get('/test/delete/{id}', 'Criterion\UI\Controller\TestController::delete');
$app->post('/hook/github', 'Criterion\UI\Controller\HookController::github');

$app->run();
