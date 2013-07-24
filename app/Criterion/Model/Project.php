<?php
namespace Criterion\Model;

class Project extends \Criterion\Model
{
    public $collection = 'projects';

    public function __construct($query = null, $existing = null)
    {
        $raw_query = $query;
        parent::__construct($query, $existing);

        if ( ! $this->exists && isset($raw_query['repo']))
        {
            $this->repo = $raw_query['repo'];
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
            $ssh_key_helper = new \Criterion\Helper\SshKey();
            $ssh_key_helper->generate($ssh_key_file);
            $this->ssh_key = array(
                'public' => $ssh_key_helper->getPublicKey(),
                'private' => $ssh_key_helper->getPrivateKey()
            );
            $ssh_key_helper->destroy();
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
