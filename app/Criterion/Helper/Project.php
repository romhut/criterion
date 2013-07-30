<?php
namespace Criterion\Helper;

class Project extends \Criterion\Helper
{
    public static function sshKeyFile($project)
    {
        $path = KEY_DIR . '/' . $project->id;

        if ( ! is_file($path)) {
            file_put_contents($path, $project->ssh_key['private']);
        }

        chmod($path, 0600);

        return $path;
    }
}
