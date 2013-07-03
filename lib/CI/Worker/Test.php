<?php
$root = dirname(dirname(dirname(__DIR__)));

include $root . '/vendor/autoload.php';

# Reverse Worker Code
$worker= new GearmanWorker();
$worker->addServer('127.0.0.1', 4730);
$worker->addFunction('test', 'test', $root);
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