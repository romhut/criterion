<?php
namespace Criterion\Model;

class Project extends \Criterion\Model
{
    public $collection = 'projects';

    public function __construct($query = null, $existing = null)
    {
        parent::__construct($query, $existing);

        if ( ! $this->exists && isset($query['repo']))
        {
            $this->repo = $query['repo'];
            $this->github = array(
                'token' => ''
            );
            $this->email = '';
            $this->short_repo = \Criterion\Helper\Repo::short($this->repo);
            $this->provider = \Criterion\Helper\Repo::provider($this->repo);
            $this->last_run = new \MongoDate();
            $this->status = array(
                'code' => '2',
                'message' => 'New'
            );

            $ssh_key_file = KEY_DIR . '/' . $this->id;
            exec('ssh-keygen -t rsa -q -f "' . $ssh_key_file . '" -N "" -C "ci@criterion"', $ssh_key, $response);
            $this->ssh_key = array(
                'public' => file_get_contents($ssh_key_file . '.pub'),
                'private' => file_get_contents($ssh_key_file)
            );

            // Remove the SSH files due to permissions issue, let PHP generate them later on.
            exec('rm ' . $ssh_key_file);
            exec('rm ' . $ssh_key_file . '.pub');
        }
    }

    public function getTests()
    {
        $tests = $this->app->db->tests->find(array(
            'project_id' => $this->id
        ))->sort(array(
            'started' => -1
        ));

        $test_models = array();
        foreach ($tests as $test)
        {
            $test_models[] = new Test(null, $test);
        }

        return $test_models;
    }
}