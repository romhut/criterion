<?php

define('ROOT', __DIR__);
define('CONFIG_FILE', ROOT . '/src/Config/config.json');
define('DATA_DIR', ROOT  . '/data');
define('TEST_DIR', DATA_DIR . '/tests');
define('KEY_DIR', DATA_DIR . '/keys');

// Make sure system has been initialized
if (! file_exists(__DIR__ . '/vendor/autoload.php')) {
    echo "\n";
    echo "  You need to install dependencies. Please run the following command:\n";
    echo "  \033[32mcurl -sL https://getcomposer.org/installer | php && php composer.phar install\033[0m\n";
    echo "\n";
    exit;
}

include __DIR__ . '/vendor/autoload.php';
$app = new Criterion\Console\Application('Criterion', '1.0');

$mongo = new MongoMinify\Client();
$app->setMongo($mongo);
$app->addCommands(array(
    new Criterion\Console\Command\TestCommand('test')
));

$app->run();
