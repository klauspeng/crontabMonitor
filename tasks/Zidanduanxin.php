<?php

/**
 * Desc : Zidanduanxin
 * User : liupeng
 * Date : 2019-02-12
 */
class Zidanduanxin extends \Core\TaskBase
{
    private $currentTime;
    private $maxExpKey = 'zddx_max_exp';
    private $maxGoldKey = 'zddx_max_gold';
    private $maxReqKey = 'zddx_max_req';
    private $signCacheKey = 'zidanduanxin_sign';
    private $currentReqCount = 0;
    private $reqLimit = 50;

    public function run()
    {
        // 当前时间
        $this->currentTime = getMillisecond();

        // 获取我的摇钱树信息
        $this->getMyTreeInfo();

        // 朋友的摇钱树
        $this->friendTreeInfo();

        // 签到
        $this->sign();
    }

    /**
     * 获取我的摇钱树信息
     */
    public function getMyTreeInfo()
    {
        // 获取
        $this->curl->setCookieString($this->config['cookie']);
        $res = $this->curl->get($this->config['treeInfoUrl']);
        $res = stdObjectToArray($res);

        // 收取
        if ($this->currentTime >= $res['next_harvest_time']) {
            $this->treeAction('HARVEST', $res['tree_id']);
        }

        // 浇水
        if ($this->currentTime >= $res['next_watering_time']) {
            $this->treeAction('WATERING', $res['tree_id']);
        }

        // 除虫
        if ($this->currentTime >= $res['next_kill_bug_time']) {
            $this->treeAction('KILL_BUG', $res['tree_id']);
        }

        // 修剪
        if ($this->currentTime >= $res['next_prune_time']) {
            $this->treeAction('PRUNE', $res['tree_id']);
        }
    }

    /**
     * 朋友的摇钱树
     */
    public function friendTreeInfo()
    {
        // 获取
        $this->curl->setCookieString($this->config['cookie']);
        $res         = $this->curl->get($this->config['friendInfoUrl']);
        $res         = stdObjectToArray($res);
        $isMaxExpKey = $this->cache->get($this->maxExpKey);

        $friendList = $res['friend_list'];

        // 循环动作
        foreach ($friendList as $item) {

            if ($this->currentReqCount >= $this->reqLimit) {
                info('请求受限');
                $this->cache->set($this->maxReqKey, 1, 60);
                return FALSE;
            }

            if ($this->cache->get($this->maxReqKey)){
                info('请求受限2');
                return FALSE;
            }

            // 是否达到最大经验
            if (!$isMaxExpKey) {
                // 浇水
                if ($this->currentTime >= $item['next_watering_time']) {
                    $this->treeAction('WATERING', $item['tree_id'], $item['user_id']);
                }

                // 除虫
                if ($this->currentTime >= $item['next_kill_bug_time']) {
                    $this->treeAction('KILL_BUG', $item['tree_id'], $item['user_id']);
                }

                // 修剪
                if ($this->currentTime >= $item['next_prune_time']) {
                    $this->treeAction('PRUNE', $item['tree_id'], $item['user_id']);
                }
            }

            if ($item['is_allow_steal'] && !$this->cache->get($this->maxGoldKey)) {
                // 收取
                if ($this->currentTime >= $item['next_harvest_time']) {
                    $res = $this->treeAction('STEAL', $item['tree_id'], $item['user_id']);

                    // 是否已到最大金币
                    if (!isset($res['code']) && isset($res['effect_gold_count']) && $res['effect_gold_count'] == 0 && (!$this->cache->get($this->maxGoldKey))) {
                        // 缓存至明天
                        $this->cache->set($this->maxGoldKey, 1, getExpireTime(0));
                        info('已达到当天最大金币值');
                    }
                }
            }
        }
    }

    /**
     * 摇钱树动作
     * @param      $action
     * @param      $treeId
     * @param bool $ownerId
     * @return mixed
     */
    private function treeAction($action, $treeId, $ownerId = FALSE)
    {
        if (!$action || !$treeId)
            return FALSE;

        // 组织数据
        $postData = [
            'action'  => $action,
            'tree_id' => $treeId,
        ];
        $ownerId && $postData['owner_id'] = $ownerId;

        // 请求
        $this->curl->setCookieString($this->config['cookie']);
        $this->curl->setHeader('Content-Type', 'application/json');
        $res = $this->curl->post($this->config['treeActionUrl'], $postData);
        $res = stdObjectToArray($res);
        info("摇钱树动作($action-$treeId)结果：", $res);

        if (isset($res['code']) && $res['code'] == 10) {
            sendEmail('摇钱树cookie过期', '摇钱树cookie过期');
        }

        // 是否已到最大经验
        if (!isset($res['code']) && isset($res['effect_exp']) && $res['effect_exp'] == 0 && (!$this->cache->get($this->maxExpKey))) {
            // 缓存至明天
            $this->cache->set($this->maxExpKey, 1, getExpireTime(0));
            info('已达到当天最大经验值');
        }

        $this->currentReqCount++;

        return  $res;
    }


    public function sign()
    {
        // 判断今天是否签到
        if ($this->cache->get($this->signCacheKey)) {
            return FALSE;
        }

        // 组织cookie POST请求
        $this->curl->setHeader('Content-Type', 'application/json');
        $this->curl->setCookieString($this->config['cookie']);
        $this->curl->post($this->config['signUrl'], '{}');
        if ($this->curl->error) {
            info('请求失败:', $this->curl->errorCode . ': ' . $this->curl->errorMessage);
            return FALSE;
        }
        $repData = stdObjectToArray($this->curl->response);

        // 校验是否成功
        if (!isset($res['code'])) {
            // 缓存至明天
            $this->cache->set($this->signCacheKey, 1, getExpireTime());
        }
        else {
            sendEmail('聊天宝签到失败！', '聊天宝签到失败！更换签到链接！');
        }

        info('聊天宝签到结果：', $repData);
    }

}