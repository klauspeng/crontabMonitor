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
            return false;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->config['singUrl']);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // curl_setopt ($ch, CURLOPT_COOKIE , $cookie );
        $data = curl_exec($ch);
        curl_close($ch);

        // 校验是否成功
        if(strpos($data,'执行签到任务成功')===false){
            sendEmail('18211089602@139.com','多点签到失败！','多点签到失败！更换签到链接！');
        }else{
            $this->cache->set($this->signCacheKey, 1, getExpireTime());
        }

        info('签到结果：', $data);

    }
}
