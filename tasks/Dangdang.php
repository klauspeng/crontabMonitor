<?php

use Core\TaskBase;

/**
 * Desc : Dangdang 当当
 * User : liupeng
 * Date : 2019-04-21
 */
class Dangdang extends TaskBase
{
    private $signCacheKey = 'dangdang';

    public function run()
    {
        // 签到
        $this->sign();
    }

    /**
     * 签到
     */
    public function sign()
    {
        // 判断今天是否签到
        if ($this->cache->get($this->signCacheKey)) {
            return false;
        }

        // POST请求
        $this->curl->setCookieString($this->config['cookie']);
        $this->curl->get($this->config['signUrl']);
        if ($this->curl->error) {
            info('请求失败:', $this->curl->errorCode . ': ' . $this->curl->errorMessage);
            return false;
        }
        $data = json_decode($this->curl->response, true);

        if (isset($data['errorCode']) && $data['errorCode'] == 0) {
            $this->cache->set($this->signCacheKey, 1, getExpireTime());
            $this->flipCard();
        } else {
            sendEmail('当当签到失败！', json_encode($data));
        }

        info('当当签到结果：', json_encode($data, JSON_UNESCAPED_UNICODE));
    }

    /**
     * 翻牌
     */
    public function flipCard()
    {
        // POST请求
        $this->curl->setCookieString($this->config['cookie']);
        $this->curl->get($this->config['flipCardUrl']);
        if ($this->curl->error) {
            info('请求失败:', $this->curl->errorCode . ': ' . $this->curl->errorMessage);
            return false;
        }
        $data = json_decode($this->curl->response, true);
        info('当当翻牌结果：', json_encode($data, JSON_UNESCAPED_UNICODE));
    }

}