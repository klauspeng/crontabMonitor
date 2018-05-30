<?php

/**
 * Project :crontabMonitor
 * User: Klaus
 * Date: 2018.04.03 21:24
 */
class Dmall extends \Core\TaskBase
{
    private $signCacheKey = 'dmall_sign';

    public function run()
    {
        // 签到
        $this->signIn();
    }

    /**
     * 签到
     */
    public function signIn()
    {

        // 判断今天是否签到
        if ($this->cache->get($this->signCacheKey)) {
            return FALSE;
        }

        // GET请求
        $this->curl->get($this->config['singUrl']);
        if ($this->curl->error) {
            info('请求失败:', $this->curl->errorCode . ': ' . $this->curl->errorMessage);
            return FALSE;
        }
        $data = $this->curl->response;

        // 校验是否成功
        if (strpos($data, '执行签到任务成功') === FALSE) {
            sendEmail('多点签到失败！', '多点签到失败！更换签到链接！');
        } else {
            $this->cache->set($this->signCacheKey, 1, getExpireTime());
        }

        info('多点签到结果：', $data);

    }
}
