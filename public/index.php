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

$app->register(new Silex\Provider\SessionServiceProvider());


// Autehntication
$app->before(function() use ($app) {

    $authenticated = false;
    $app['user'] = false;

    if ($app['session']->get('user') || ! isset($app['criterion']->config['visibility']) || $app['criterion']->config['visibility'] !== 'public')
    {
        $path_info = pathinfo($app['request']->getPathInfo());
        $uri = $app['request']->server->get('REQUEST_URI');

        $allowed_urls = array('/auth/login', '/hook/github');

        if (in_array($uri, $allowed_urls) || (isset($path_info['extension']) && in_array($path_info['extension'], array('png', 'jpg'))))
        {
             $authenticated = true;
        }
        else
        {
            if ($app['session']->get('user'))
            {
                $session = $app['session']->get('user');
                $user = new \Criterion\Model\User(array(
                    'username' => $session['username']
                ));

                if ($user->exists)
                {
                    $app['user'] = $user;
                    $authenticated = true;
                }
            }
        }

        if (! $authenticated)
        {
            return $app->abort(403, 'You need to be authenticated to access Criterion');
        }
    }
});

$app->error(function(\Exception $e, $code) use($app) {

    $allowed_codes = array(401, 404, 403);
    if ( ! in_array($code, $allowed_codes))
    {
        $code = 404;
    }

    $app['user'] = false;
    $app['token'] = false;

    return $app['twig']->render('Error/'.$code.'.twig', array(
        'error' => $e
    ));
});

$app->get('/', 'Criterion\UI\Controller\ProjectsController::all');

$app->match('/auth/login', 'Criterion\UI\Controller\AuthController::login')->method('POST|GET');
$app->match('/auth/tokens', 'Criterion\UI\Controller\AuthController::tokens')->method('POST|GET');
$app->get('/auth/logout', 'Criterion\UI\Controller\AuthController::logout');

$app->post('/project/create', 'Criterion\UI\Controller\ProjectsController::create');
$app->match('/project/{id}', 'Criterion\UI\Controller\ProjectsController::view')->method('POST|GET');
$app->get('/project/run/{id}', 'Criterion\UI\Controller\ProjectsController::run');
$app->get('/project/delete/{id}', 'Criterion\UI\Controller\ProjectsController::delete');
$app->get('/status/{vendor}/{package}.{extension}', 'Criterion\UI\Controller\ProjectsController::status')->assert('extension', '(jpg|png)');

$app->get('/test/{id}', 'Criterion\UI\Controller\TestController::view');
$app->get('/test/status/{id}', 'Criterion\UI\Controller\TestController::status');
$app->get('/test/delete/{id}', 'Criterion\UI\Controller\TestController::delete');

$app->post('/hook/github/{token}', 'Criterion\UI\Controller\HookController::github');

$app->run();
