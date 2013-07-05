<?php

namespace Criterion\Test;

class GithubHelperTest extends TestCase
{

    public function testSSHUrl()
    {
        $url = 'https://github.com/romhut/api';
        $new_url = \Criterion\Helper\Github::toSSHUrl($url);
        $this->assertEquals($new_url, 'git@github.com:romhut/api');
    }

}
