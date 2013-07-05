<?php

include __DIR__ . '/vendor/autoload.php';
$app = new Criterion\Console\Application('Criterion', '1.0');

define('ROOT', __DIR__);
define('TEST_DIR', ROOT . '/tests');

$mongo = new MongoMinify\Client();
$app->setMongo($mongo);
$app->addCommands(array(
    new Criterion\Console\Command\TestCommand('test')
));

$app->run();
