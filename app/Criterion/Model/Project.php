<?php
namespace Criterion\Model;

class Project extends \Criterion\Model
{
    public $collection = 'projects';
    public $serverConfig = array();
    public $serverConfigWhitelist = array(
        'repo' => '',
        'email' => '',
        'ssh_key' => array(),
        'enviroment_variables' => array(),
        'github' => array(),
        'script' => '',
        'setup' => '',
        'fail' => '',
        'pass' => ''
    );

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
            $this->enviroment_variables = array(
                'APP_ENV=testing'
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

    public function setServerConfig($data)
    {
        foreach ($data as $key => $value) {
            if (array_key_exists($key, $this->serverConfigWhitelist)) {
                if ($key === 'enviroment_variables' && is_array($value)) {
                    foreach ($value as $enviroment_variable_key => $enviroment_variable) {
                        if (preg_match('/^[a-z][a-z0-9_]+=[a-z][a-z0-9_]+$/i', $enviroment_variable)) {
                            $config_data[$key][$enviroment_variable_key] = $enviroment_variable;
                        }
                    }

                    if (is_array($config_data[$key])) {
                        $config_data[$key] = array_unique($config_data[$key]);
                    }
                } else {
                    $config_data[$key] = $value;
                }
            }
        }

        $this->serverConfig = array_merge($this->serverConfigWhitelist, $config_data);
        $this->data = array_merge($this->data, $this->serverConfig);

        return $this->serverConfig;
    }

    public function hasServerConfig()
    {
        $this->getServerConfig();
        foreach ($this->serverConfig as $config) {
            if (count($config)) {
                return true;
            }
        }

        return false;
    }

    public function getServerConfig()
    {
        $data = array_merge($this->serverConfigWhitelist, $this->data);

        foreach ($data as $key => $value) {
            if (array_key_exists($key, $this->serverConfigWhitelist)) {
                $this->serverConfig[$key] = $value;
            }
        }

        return $this->serverConfig;
    }
}
