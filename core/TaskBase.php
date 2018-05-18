<?php

/**
 * Project :crontabMonitor
 * User: Klaus
 * Date: 2018.04.03 21:20
 */

namespace Core;

use Curl\Curl;

abstract class TaskBase
{
    // 配置
    protected $config = null;
    // 缓存
    protected $cache = null;
    // curl类
    protected $curl = null;

    /**
     * TaskBase constructor.
     *
     * @param array $config 运行所需配置
     */
    public function __construct($config)
    {
        $this->config = $config;
        $this->cache  = new FileCache();
        $this->curl   = new Curl();
    }

    /**
     * 运行方法
     * @return mixed
     */
    abstract public function run();

    /**
     * 析构方法
     */
    public function __destruct()
    {
        // 清除缓存文件
        // $this->cache->clear();
    }

}