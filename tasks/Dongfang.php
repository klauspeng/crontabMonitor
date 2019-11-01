<?php

use Core\TaskBase;

/**
 * Desc : Dongfang.php 东方头条
 * User : liupeng
 * Date : 2019/3/18
 */
class Dongfang extends TaskBase
{
    private $hourCacheKey = 'dongfang_hour_award';
    private $treeCacheKey = 'dongfang_tree_award';

    public function run()
    {
        // key
        $this->hourCacheKey .= $this->index;
        $this->treeCacheKey .= $this->index;

        $this->getHourAward();
        $this->getTreeAward();
    }

    /**
     * 获取时段奖励
     */
    public function getHourAward()
    {
        // 判断今天是否获取
        if ($this->cache->get($this->hourCacheKey)) {
            return false;
        }

        $info = $this->curl->post($this->config['hourAward'], $this->config['hourAwardData']);
        $info = stdObjectToArray($info);
        info($this->index . '东方头条时段奖励：', $info['data']);
        $this->cache->set($this->hourCacheKey, 1, 3600);
    }

    /**
     * 获取摇钱树奖励
     */
    public function getTreeAward()
    {
        // 判断今天是否获取
        if ($this->cache->get($this->treeCacheKey)) {
            return false;
        }

        $info = $this->curl->post($this->config['treeAward'], $this->config['treeAwardData']);
        $info = stdObjectToArray($info);
        info($this->index . '东方头条摇钱树奖励：', $info['data']);
        $this->cache->set($this->treeCacheKey, 1, $info['data']['remaining_time']);
    }

}