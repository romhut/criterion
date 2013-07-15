<?php

define('ROOT', dirname(__DIR__));
define('CONFIG_FILE', ROOT . '/config.json');
define('DATA_DIR', ROOT  . '/data');
define('TEST_DIR', DATA_DIR . '/tests');
define('KEY_DIR', DATA_DIR . '/keys');

include dirname(__DIR__) . '/vendor/autoload.php';
$app = new Silex\Application();
$app['criterion'] = new Criterion\Application();

if ( ! $app['criterion']->config)
{
    echo 'You must install Criterion first by running: "bin/cli install"';
    exit;
}
$app['debug'] = true;
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => dirname(__DIR__) . '/app/Criterion/UI/View',
));

// Autehntication
$app->before(function() use ($app) {

    $app['user'] = false;
    $authenticated = false;

    $path_info = pathinfo($app['request']->getPathInfo());
    if (isset($path_info['extension']) && in_array($path_info['extension'], array('png', 'jpg')))
    {
         $authenticated = true;
    }
    else
    {
        if ($app['request']->server->get('PHP_AUTH_USER'))
        {
            $username = strtolower($app['request']->server->get('PHP_AUTH_USER'));
            $password = $app['request']->server->get('PHP_AUTH_PW');

            $user = new \Criterion\Model\User($username);

            if ($user->exists)
            {
                if (password_verify($password, $user->password))
                {
                    $app['user'] = $user;
                    $authenticated = true;
                }
            }
        }
    }

    if (! $authenticated)
    {
        return $app->abort(401, 'You need to be authenticated to access Criterion');
    }
});

$app->error(function(\Exception $e, $code) use($app) {

    $allowed_codes = array(401, 404, 403);
    if ( ! in_array($code, $allowed_codes))
    {
        $code = 404;
    }

    if ($code === 401)
    {
        header('WWW-Authenticate: Basic realm="Criterion"');
        header('HTTP/1.0 401 Unauthorized');
    }

    return $app['twig']->render('Error/'.$code.'.twig');
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
