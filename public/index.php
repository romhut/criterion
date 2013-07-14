<?php

define('ROOT', dirname(__DIR__));
define('CONFIG_FILE', ROOT . '/config.json');
define('DATA_DIR', ROOT  . '/data');
define('TEST_DIR', DATA_DIR . '/tests');
define('KEY_DIR', DATA_DIR . '/keys');

if ( ! file_exists(CONFIG_FILE))
{
    echo 'You must install Criterion first by running: "bin/cli install"';
    exit;
}

include dirname(__DIR__) . '/vendor/autoload.php';

$app = new Silex\Application();
$app['debug'] = true;

$app->register(new MongoMinify\Silex\ServiceProvider(), array(
    'mongo.server' => 'mongodb://127.0.0.1:27017/criterion',
));

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => dirname(__DIR__) . '/app/Criterion/UI/View',
));

// Autehntication
$app->before(function() use ($app) {

    $path_info = pathinfo($app['request']->getPathInfo());
    $authenticated = false;
    if (in_array($path_info['extension'], array('png', 'jpg')))
    {
         $authenticated = true;
    }
    else
    {
        if ($app['request']->server->get('PHP_AUTH_USER')) {
            $username = strtolower($app['request']->server->get('PHP_AUTH_USER'));
            $password = $app['request']->server->get('PHP_AUTH_PW');
            $user = $app['mongo']->selectCollection('users')->findOne(array('_id' => $username));
            if ($user) {
                if (password_verify($password, $user['password'])) {
                    $authenticated = true;
                }
            }
        }
    }

    if (! $authenticated) {
        header('WWW-Authenticate: Basic realm="Criterion"');
        header('HTTP/1.0 401 Unauthorized');
        echo $app['twig']->render('Error/401.twig');
        exit;
    }
});

$app->get('/', 'Criterion\UI\Controller\ProjectsController::all');
$app->post('/project/create', 'Criterion\UI\Controller\ProjectsController::create');
$app->match('/project/{id}', 'Criterion\UI\Controller\ProjectsController::view')->method('POST|GET');
$app->get('/project/run/{id}', 'Criterion\UI\Controller\ProjectsController::run');
$app->get('/project/delete/{id}', 'Criterion\UI\Controller\ProjectsController::delete');
$app->get('/status/{vendor}/{package}.{extension}', 'Criterion\UI\Controller\ProjectsController::status')->assert('extension', '(jpg|png)');
$app->get('/test/{id}', 'Criterion\UI\Controller\TestController::view');
$app->get('/test/status/{id}', 'Criterion\UI\Controller\TestController::status');
$app->get('/test/delete/{id}', 'Criterion\UI\Controller\TestController::delete');
$app->post('/hook/github', 'Criterion\UI\Controller\HookController::github');

$app->run();
