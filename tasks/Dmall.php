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
        $this->signCacheKey .= $this->index;
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
            // 奖励领取
            $this->getAward();
        }

        info('多点签到结果：', $data);

    }

    /**
     * 领取奖励 -- 签到成功之后领取
     */
    public function getAward()
    {
        // GET请求
        $this->curl->get($this->config['infoUrl']);
        if ($this->curl->error) {
            info('请求失败:', $this->curl->errorCode . ': ' . $this->curl->errorMessage);
            return FALSE;
        }
        $data = $this->curl->response;
        $data = str_replace(["jQuery214009259084913173321_1549874781187({'result':", '})'], '', $data);
        $data = json_decode($data, TRUE);

        if ($data['code'] == '0000') {
            foreach ($data['data']['currentMonthContinueProgress'] as $currentMonthContinueProgress) {
                if ($currentMonthContinueProgress['status'] == 2) {
                    $this->curl->get($this->config['getAwardUrl'] . $currentMonthContinueProgress['taskId']);
                    info('领取积分结果：', $this->curl->response);
                }
            }

            foreach ($data['data']['currentMonthAddProgress'] as $currentMonthAddProgres) {
                if ($currentMonthAddProgres['status'] == 2) {
                    $this->curl->get($this->config['getAwardUrl'] . $currentMonthAddProgres['taskId']);
                    info('领取积分结果：', $this->curl->response);
                }
            }

            foreach ($data['data']['notCurrentMonthContinueProgress'] as $notCurrentMonthContinueProgres) {
                if ($notCurrentMonthContinueProgres['status'] == 2) {
                    $this->curl->get($this->config['getAwardUrl'] . $notCurrentMonthContinueProgres['taskId']);
                    info('领取积分结果：', $this->curl->response);
                }
            }
        } else {
            info('领取积分失败：', $this->curl->response);
        }

    }
}
