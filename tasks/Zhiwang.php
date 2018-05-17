<?php

/**
 * Project :crontabMonitor
 * User: Klaus
 * Date: 2018.04.03 21:24
 */

/**
 * 指旺签到
 * Class Zhiwang
 */
class Zhiwang extends \Core\TaskBase
{
    private $signCacheKey = 'zhiwang_sign';

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

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->config['singUrl']);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_COOKIE, $this->config['cookie']);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, ['task_name' => 'privilege_sign']);
        $data = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($data, TRUE);

        // 校验是否成功
        if ($data['status'] == 'drawed' || $data['status'] == 'success') {
            $this->cache->set($this->signCacheKey, 1, getExpireTime());
        } else {
            sendEmail('指旺签到失败！', '指旺签到失败！更换签到链接！');
        }

        info('签到结果：', $data);

    }
}
