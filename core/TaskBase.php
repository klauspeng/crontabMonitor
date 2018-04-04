<?php

/**
 * Project :crontabMonitor
 * User: Klaus
 * Date: 2018.04.03 21:20
 */
namespace Core;
abstract class TaskBase
{
    protected $config = null;
    protected $cache = null;

    /**
     * TaskBase constructor.
     * @param array $config 运行所需配置
     */
    public function __construct($config)
    {
        $this->config = $config;
        $this->cache = new FileCache();
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