<?php

namespace Criterion\Service;

class Git extends \Criterion\Service
{
    /**
     * Required optopms
     * @var array
     */
    protected $requiredOptions = [
        'repo'
    ];

    /**
     * Name of Service
     * @var string
     */
    protected $name = 'git';

    /**
     * Return the final command to run
     * @author Scott Robertson <scottymeuk@gmail.com>
     * @return string
     */
    public function getCommand()
    {
        return sprintf(
            'git clone %s %s',
            $this->repo,
            $this->getJob()->getPath()
        );
    }
}
