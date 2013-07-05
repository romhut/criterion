<?php
namespace Criterion\Helper;

class Github
{
    public static function toSSHUrl($url)
    {
        $url = str_replace(array('https://','.com/'), array('git@','.com:'), $url);
        return $url;
    }
}
