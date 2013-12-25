<?php

namespace Criterion\Hook;

class Bitbucket extends \Criterion\Hook
{
    protected $name = 'bitbucket';

    public function notify($event, \Criterion\Model\Job $job)
    {
        // Send notification
    }
}
