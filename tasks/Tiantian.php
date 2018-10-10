<?php

/**
 * 天天基金--签到
 * Project :crontabMonitor
 * User: Klaus
 * Date: 2018.10.10 09:24
 */
class Tiantian extends \Core\TaskBase
{

    private $signCacheKey = 'tiantian';

    public function run()
    {
        // 签到
        $this->sign();
    }


    public function sign()
    {
        // 判断今天是否签到
        if ($this->cache->get($this->signCacheKey)) {
            return FALSE;
        }

        // POST请求
        $this->curl->post($this->config['singUrl'], $this->config['data']);
        if ($this->curl->error) {
            info('请求失败:', $this->curl->errorCode . ': ' . $this->curl->errorMessage);
            return FALSE;
        }
        $data = stdObjectToArray($this->curl->response);

        if (isset($data['Result']['responseObjects'][0])) {
            $respose = $data['Result']['responseObjects'][0];
            // 已签到则缓存
            if ($respose['IsCurrentDaySigned']) {
                $this->cache->set($this->signCacheKey, 1, getExpireTime());
            }

            // 超过500就邮件提醒兑换
            if ($respose['Points'] >= 500) {
                sendEmail('天天基金可兑换了', '目前积分' . $respose['Points']);
            }
        } else {
            sendEmail('天天基金签到失败！', json_encode($data));
        }

        info('天天基金签到结果：', json_encode($data,JSON_UNESCAPED_UNICODE));
    }
}