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

        // 组织cookie
        $cookie = str_replace(';', '&', $this->config['cookie']);
        parse_str($cookie, $cookies);
        $this->curl->setCookies($cookies);

        // post请求
        $this->curl->post($this->config['singUrl'], ['task_name' => 'privilege_sign']);
        if ($this->curl->error) {
            info('请求失败:', $this->curl->errorCode . ': ' . $this->curl->errorMessage);
            return FALSE;
        }

        // 解析数据
        $repData = stdObjectToArray($this->curl->response);

        // 校验是否成功
        if ($repData['status'] == 'drawed' || $repData['status'] == 'success') {
            $this->cache->set($this->signCacheKey, 1, getExpireTime());
        } else {
            sendEmail('指旺签到失败！', '指旺签到失败！更换签到链接！');
        }

        info('指旺签到结果：', $repData);

    }
}
