<?php

require_once ROOT . '/vendor/autoload.php';
$app = new \Silex\Application();

// Setup Config
$enviroment = getenv('APP_ENV') ?: 'development';
$app->register(
    new Igorw\Silex\ConfigServiceProvider(CONFIG_PATH . '/' . $enviroment . '.yml')
);

$app['mongo'] = $app->share(function () {
    $mongo = new \MongoClient();
    return $mongo->criterion;
});

\Criterion\Application::setApp($app);

return $app;
