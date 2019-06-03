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
    private $healthCacheKey = 'zhiwang_health';

    private $signGiftDays = [3, 7, 15, 29];
    private $remindScore = 50000;

    public function run()
    {
        // 签到
        // $this->signIn();

        // 走路赚钱
        // $this->healthInvest();

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

        // 组织cookie POST请求
        $this->curl->setCookieString($this->config['cookie']);
        $this->curl->post($this->config['singUrl'], ['task_name' => 'privilege_sign']);
        if ($this->curl->error) {
            info('请求失败:', $this->curl->errorCode . ': ' . $this->curl->errorMessage);
            return FALSE;
        }
        $repData = stdObjectToArray($this->curl->response);

        // 校验是否成功
        if ($repData['status'] == 'drawed' || $repData['status'] == 'success') {
            // 缓存至明天
            $this->cache->set($this->signCacheKey, 1, getExpireTime());

            // 领礼包
            if (in_array($repData['keepday'], $this->signGiftDays)) {
                $this->getSignGift($repData['keepday']);
            }
        } else {
            sendEmail('指旺签到失败！', '指旺签到失败！更换签到链接！');
        }

        // 兑换提醒
        $this->exchangeGift();

        info('指旺签到结果：', $repData);

    }

    /**
     * 走路赚钱
     */
    public function healthInvest()
    {
        $hour = date("G");
        if ($hour != 21 || $this->cache->get($this->healthCacheKey)) {
            return FALSE;
        }

        $url = 'https://www.91zhiwang.com/activities/health_invest/send_award?step=';
        // 组织cookie POST请求
        $this->curl->setCookieString($this->config['cookie']);
        $data = stdObjectToArray($this->curl->get($url . '5000'));
        info('指旺走路赚钱-5000-结果：', $data);
        $data = stdObjectToArray($this->curl->get($url . '8000'));
        info('指旺走路赚钱-8000-结果：', $data);
        $data = stdObjectToArray($this->curl->get($url . '12000'));
        info('指旺走路赚钱-12000-结果：', $data);

        // 缓存
        $this->cache->set($this->healthCacheKey, 1, getExpireTime());
    }


    /**
     * 签到礼包
     */
    public function getSignGift($keepday)
    {
        $step = array_search($keepday, $this->signGiftDays);

        if ($step !== FALSE) {
            $step++;

            // 组织cookie POST请求
            $this->curl->setCookieString($this->config['cookie']);
            $this->curl->post($this->config['singGiftUrl'] . $step, ['task_name' => 'privilege_sign']);

            if ($this->curl->error) {
                info('签到礼包请求失败:', $this->curl->errorCode . ': ' . $this->curl->errorMessage);
            }

            $repData = stdObjectToArray($this->curl->response);
            info('指旺签到礼包结果：', $repData);
        }


    }

    /**
     * 兑换物品提醒
     */
    public function exchangeGift()
    {
        // 组织cookie
        $this->curl->setCookieString($this->config['cookie']);
        $data = $this->curl->get($this->config['exchangeGiftUrl']);
        info(json_encode($data));
        if (isset($data['total']) && $data['total'] >= $this->remindScore) {
            sendEmail('指旺可兑换了！' . $data['total'], '指旺可兑换了！');
        }
    }
}
