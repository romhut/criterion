<?php
namespace Criterion\Helper;

class Project
{
    public static function fromRepo($repo)
    {
        $project = array();
        $project['repo'] = $repo;
        $project['github']['token'] = null;
        $project['short_repo'] = Repo::short($project['repo']);
        $project['provider'] = Repo::provider($project['repo']);
        $project['last_run'] = new \MongoDate();
        $project['status'] = array(
            'code' => '2',
            'message' => 'New'
        );

        return $project;
    }
}
