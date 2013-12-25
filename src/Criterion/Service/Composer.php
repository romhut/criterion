<?php

namespace Criterion\Service;

class Composer extends \Criterion\Service
{

    /**
     * Setup default Composer options
     * @var array
     */
    protected $defaultOptions = [
        'method' => 'install',
        'dev' => true,
    ];

    /**
     * Name of Service
     * @var string
     */
    protected $name = 'composer';

    /**
     * Return the final command to run
     * @author Scott Robertson <scottymeuk@gmail.com>
     * @return string
     */
    public function getCommand()
    {
        return sprintf(
            'composer %s %s',
            $this->method,
            ($this->dev === true ? '--dev' : '--no-dev')
        );
    }
}
