<?php

namespace Criterion;

class Application
{
    /**
     * Holds the static Criterion Application
     * @var \Silex\Application
     */
    private static $app;

    /**
     * Return the Criterion Application object
     * @author Scott Robertson <scottymeuk@gmail.com>
     * @return \Silex\Application
     */
    public static function getApp()
    {
        return self::$app;
    }

    /**
     * Set the Criterion Application object
     * @author Scott Robertson <scottymeuk@gmail.com>
     * @param  \Silex\Application $app
     */
    public static function setApp(\Silex\Application $app)
    {
        self::$app = $app;
    }
}
