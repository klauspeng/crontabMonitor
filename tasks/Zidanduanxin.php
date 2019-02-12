<?php

/**
 * Desc : Zidanduanxin
 * User : liupeng
 * Date : 2019-02-12
 */
class Zidanduanxin extends \Core\TaskBase
{
    private $currentTime;

    public function run()
    {
        // 当前时间
        $this->currentTime = getMillisecond();

        // 获取我的摇钱树信息
        $this->getMyTreeInfo();

        // 朋友的摇钱树
        $this->friendTreeInfo();
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
        $res = $this->curl->get($this->config['friendInfoUrl']);
        $res = stdObjectToArray($res);

        $friendList = $res['friend_list'];

        // 循环动作
        foreach ($friendList as $item) {

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

            if ($item['is_allow_steal']) {
                // 收取
                if ($this->currentTime >= $item['next_harvest_time']) {
                    $this->treeAction('STEAL', $item['tree_id'], $item['user_id']);
                }
            }
        }
    }

    /**
     * 摇钱树动作
     * @param      $action
     * @param      $treeId
     * @param bool $ownerId
     */
    private function treeAction($action, $treeId, $ownerId = FALSE)
    {
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
        info("摇钱树动作($action)结果：", $res);
    }

}