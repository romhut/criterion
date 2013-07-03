<?php

include __DIR__ . '/vendor/autoload.php';
$app = new CI\Application('CI', '1.0');

define('ROOT', __DIR__);
define('TEST_DIR', ROOT . '/tests');

$mongo = new MongoClient();
$app->setMongo($mongo);
$app->addCommands(array(
    new CI\Command\TestCommand('test', $app),
));

$app->run();