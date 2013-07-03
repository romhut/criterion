<?php
$root = dirname(dirname(__DIR__));

include $root . '/vendor/autoload.php';

# Reverse Worker Code
$worker= new GearmanWorker();
$worker->addServer('127.0.0.1', 4730);
$worker->addFunction('test', 'test', $root);
$worker->addFunction('create_test', 'create_test', $root);
while ($worker->work());

function test($job, $root)
{
	$work = json_decode($job->workload(), true);

	$project = $work['project'];
	$test = $work['test'];

	echo 'Testing Project: ' . $project . "\n";
	exec("php $root/console.php test $project $test", $output);
	echo 'Done' . "\n\n";
}

function create_test($job, $root)
{
	$project = $job->workload();
	exec("php $root/console.php create_test $project", $test);
	return trim($test[0]);
}