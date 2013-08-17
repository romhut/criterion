<?php

namespace Criterion\Helper;

class SshKey extends \Criterion\Helper
{

    protected $file = '/tmp/criterion-ssh-key.pem';

    /**
     * Create a new instance
     */
    public function __construct()
    {
    }

    /**
     * Generate a new SSH Key
     * @throws \Exception If key was not generated
     * @return string     Generated private key
     */
    public function generate($file)
    {
        $dir = dirname($file);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $this->file = $file;
        exec('ssh-keygen -t rsa -q -f "' . $file . '" -N "" -C "ci@criterion"', $ssh_key, $response);
        if ($response != 0) {
            throw new \Exception('Could not generate key file');
        }

        return $this->getPrivateKey();
    }

    /**
     * Get private key
     * @return string Generated private key
     */
    public function getPrivateKey()
    {
        return file_exists($this->file) ? file_get_contents($this->file) : '';
    }

    /**
     * Get public key
     * @return string Generated public key
     */
    public function getPublicKey()
    {
        return file_exists($this->file . '.pub') ? file_get_contents($this->file . '.pub') : '';
    }

    /**
     * Destroy SSH Key
     */
    public function destroy()
    {
        if (file_exists($this->file)) {
            unlink($this->file);
        }
        if (file_exists($this->file . '.pub')) {
            unlink($this->file . '.pub');
        }

        return true;
    }
}
