<?php

namespace Criterion;

use Symfony\Component\Yaml\Yaml;

class Config
{
    /**
     * The name of the Criterion config file
     */
    const FILE = '.criterion.yml';

    /**
     * Holds the job
     * @var Criterion\Job
     */
    protected $job;

    /**
     * The path of the config file
     * @var string
     */
    protected $path;

    /**
     * Holds the config array
     * @var array
     */
    protected $config = [];

    /**
     * Holds all services
     * @var array
     */
    protected $services = [];

    /**
     * Holds all hooks
     * @var array
     */
    protected $hooks = [];

    /**
     * Initiate the config for this job
     * @author Scott Robertson <scottymeuk@gmail.com>
     * @param  Criterion\Job $job
     */
    public function __construct(\Criterion\Model\Job $job)
    {
        $this->job = $job;
        $this->path = $job->getPath() . '/' . self::FILE;

        $this->setConfig(
            $this->loadConfig()
        );

    }

    /**
     * Set the Config array
     * @author Scott Robertson <scottymeuk@gmail.com>
     * @param  array $config
     */
    public function setConfig(array $config)
    {
        $this->config = $config;
    }

    /**
     * Return the Config
     * @author Scott Robertson <scottymeuk@gmail.com>
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Load the config file for this test
     * @author Scott Robertson <scottymeuk@gmail.com>
     * @return array
     */
    protected function loadConfig()
    {
        return Yaml::parse(
            file_get_contents($this->path)
        );
    }

    /**
     * Get all services defined in this config
     * @author Scott Robertson <scottymeuk@gmail.com>
     * @return array
     */
    public function getServices()
    {
        if (empty($this->config['services'])) {
            return [];
        }

        $services = [];
        foreach ($this->config['services'] as $service => $options) {
            $service = $this->getServiceFromString($service, $options);
            if ($service) {
                $services[] = $service;
            }
        }

        return $services;
    }

    /**
     * Return a Service object from a string
     * @author Scott Robertson <scottymeuk@gmail.com>
     * @param  string $service
     * @param  array  $options
     * @return \Criterion\Service
     */
    protected function getServiceFromString($service, array $options = array())
    {
        $service = '\\Criterion\\Service\\' . ucfirst($service);
        if (class_exists($service)) {
            return new $service($this->job, $options);
        }

        return false;
    }

    /**
     * Get all Hooks defined in this config
     * @author Scott Robertson <scottymeuk@gmail.com>
     * @return array
     */
    public function getHooks()
    {
        if (empty($this->config['hooks'])) {
            return [];
        }

        $hooks = [];
        foreach ($this->config['hooks'] as $hook => $options) {
            $hook = $this->getHookFromString($hook, $options);
            if ($hook) {
                $hooks[] = $hook;
            }
        }

        return $hooks;
    }

    /**
     * Return a Hook object from a string
     * @author Scott Robertson <scottymeuk@gmail.com>
     * @param  string $hook
     * @param  array  $options
     * @return \Criterion\Hook
     */
    protected function getHookFromString($hook, array $options = array())
    {
        $hook = '\\Criterion\\Hook\\' . ucfirst($hook);
        if (class_exists($hook)) {
            return new $hook($this->job, $options);
        }

        return false;
    }
}
