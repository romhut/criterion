<?php

define('ROOT', __DIR__);
define('DATA_DIR', ROOT  . '/data');
define('TEST_DIR', DATA_DIR . '/tests');
define('KEY_DIR', DATA_DIR . '/keys');

include __DIR__ . '/vendor/autoload.php';
$app = new Criterion\Console\Application('Criterion', '1.0');

$mongo = new MongoMinify\Client();
$app->setMongo($mongo);
$app->addCommands(array(
    new Criterion\Console\Command\TestCommand('test')
));

$app->run();
