<?php

$root = dirname(dirname(dirname(__DIR__)));

include $root . '/vendor/autoload.php';

$client = new MongoMinify\Client('mongodb://127.0.0.1:27017', array('connect' => true));
$tests = $client->criterion->tests;

while (true)
{
    $test = $tests->findAndModify(array(
        'status.code' => '4'
    ), array(
        '$set' => array(
            'status' => array(
                'code' => '3',
                'message' => 'Running'
            )
        )
    ));

    if ($test)
    {
        $project = (string) $test['project_id'];
        $test = (string)  $test['_id'];

        echo 'Testing Project: ' . $project . "\n";
        exec("php $root/console.php test $test", $output);
        echo 'Done' . "\n\n";
    }

    sleep(10);
}