<?php
namespace Criterion\Helper;

class Project
{
    public static function fromRepo($repo)
    {
        $project = array();
        $project['repo'] = $repo;
        $project['github']['token'] = null;
        $project['email'] = null;
        $project['short_repo'] = Repo::short($project['repo']);
        $project['provider'] = Repo::provider($project['repo']);
        $project['last_run'] = new \MongoDate();
        $project['status'] = array(
            'code' => '2',
            'message' => 'New'
        );

        return $project;
    }

    public static function sshKeyFile($project)
    {
        $path = KEY_DIR . '/' . $project['_id'];

        if ( ! is_file($path))
        {
            file_put_contents($path, $project['ssh_key']['private']);
        }

        chmod($path, 0600);

        return $path;
    }
}
