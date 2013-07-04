<?php
$root = dirname(dirname(dirname(__DIR__)));

include $root . '/vendor/autoload.php';

# Reverse Worker Code
$worker= new GearmanWorker();
$worker->addServer('127.0.0.1', 4730);
$worker->addFunction('create_test', 'create_test', $root);
while ($worker->work());

function create_test($job, $root)
{
	$project = $job->workload();
	exec("php $root/console.php create_test $project", $test);

	$client= new \GearmanClient();
	$client->addServer('127.0.0.1', 4730);

	$client->doBackground('test', json_encode(array(
		'project' => $project,
		'test' => trim($test[0])
	)));

	return true;
}