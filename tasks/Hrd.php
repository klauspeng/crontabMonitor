<?php

/**
 * Project :crontabMonitor
 * User: Klaus
 * Date: 2018.04.03 21:24
 */
class Hrd extends \Core\TaskBase
{
    public function run()
    {
        var_dump($this->config);
    }
}