<?php
$root = dirname(dirname(__DIR__));

include $root . '/vendor/autoload.php';

# Reverse Worker Code
$worker= new GearmanWorker();
$worker->addServer('127.0.0.1', 4730);
$worker->addFunction('build', 'build', $root);
while ($worker->work());

function build($job, $root)
{
	$project = $job->workload();
	echo 'Testing Project: ' . $project . "\n";
	exec("php $root/console.php test $project", $output);
	echo 'Done' . "\n\n";
}