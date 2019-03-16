<?php
/**
 * Created by PhpStorm.
 * User: Liupeng
 * Date: 2018-05-22
 * Time: 12:46
 */

/**
 * 今日头条签到
 * Class Toutiao
 */
class Toutiao extends \Core\TaskBase
{
    private $signCacheKey = 'toutiao_sign';
    private $boxCacheKey = 'toutiao_hour_box';
    private $shareCacheKey = 'toutiao_share';
    private $searchCacheKey = 'toutiao_search';

    public function run()
    {
        // 初始化
        $this->init();

        // 签到
        $this->signIn();

        // 开启宝箱
        $this->openBox();

        // 晒收入
        $this->shareAward();

        // 搜索奖励
        $this->searchAward();

    }

    public function init()
    {
        $this->signCacheKey .= $this->index;
        $this->boxCacheKey .= $this->index;
        $this->searchCacheKey .= $this->index;
        $this->shareCacheKey .= $this->index;
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

        // 设置cookie POST请求
        $this->curl->setCookieString($this->config['cookie']);
        $this->curl->post($this->config['singUrl']);
        if ($this->curl->error) {
            info($this->index.'请求失败:', $this->curl->errorCode . ': ' . $this->curl->errorMessage);
            return FALSE;
        }
        $data = stdObjectToArray($this->curl->response);

        // 校验是否成功
        if ($data['err_no'] != 0 && $data['err_no'] != 1025) {
            sendEmail('今日头条签到失败！', '今日头条签到失败！更换签到链接！');
        } else {
            $this->cache->set($this->signCacheKey, 1, getExpireTime());
        }

        info($this->index.'今日头条签到结果：', $data);

        // 账户监控
        // $this->userInfo();
    }

    /**
     * 开启宝箱
     */
    public function openBox()
    {
        // 判断今天是否签到
        if ($this->cache->get($this->boxCacheKey)) {
            return FALSE;
        }

        // 设置cookie POST请求
        $this->curl->setCookieString($this->config['cookie']);
        $this->curl->post($this->config['hourBoxUrl']);
        if ($this->curl->error) {
            info($this->index.'请求失败:', $this->curl->errorCode . ': ' . $this->curl->errorMessage);
            return FALSE;
        }
        $data = stdObjectToArray($this->curl->response);

        // 校验是否成功
        if ($data['err_no'] == 0) {
            $this->cache->set($this->boxCacheKey, 1, $data['data']['next_treasure_time'] - $data['data']['current_time']);
        }

        info($this->index.'今日头条开宝箱结果：', $data);
    }

    /**
     * 晒收入
     */
    public function shareAward()
    {
        // 判断今天是否分享
        if ($this->cache->get($this->shareCacheKey)) {
            return FALSE;
        }

        // 设置cookie POST请求
        $this->curl->setHeader('Content-Type', 'application/json');
        $this->curl->setCookieString($this->config['cookie']);

        for ($i = 0; $i < 3; $i++) {
            $this->curl->post($this->config['shareUrl'], ['task_id' => 100]);
            $data = stdObjectToArray($this->curl->response);
            info($this->index.'今日头条晒收入结果：', $data);
        }

        $this->cache->set($this->shareCacheKey, 1, getExpireTime());
    }


    /**
     * 搜索
     */
    public function searchAward()
    {
        // 判断今天是否搜索
        if ($this->cache->get($this->searchCacheKey)) {
            return FALSE;
        }

        // 设置cookie
        $this->curl->setCookieString($this->config['cookie']);
        $res = $this->curl->get($this->config['searchSugUrl']);
        $res = stdObjectToArray($res);
        $count = 0;

        for ($i = 0; $i < count($res['data']['suggest_words']); $i++) {
            $this->curl->setCookieString($this->config['cookie']);
            $data = $this->curl->get($this->config['searchUrl'] . urlencode($res['data']['suggest_words'][$i]));
            $data = stdObjectToArray($data);
            if (isset($data['keyword'])){
                info($this->index.'今日头条搜索结果：', $data['keyword']);
                $count++;
                info($this->index.'今日头条搜索次数：', $count);
            }
        }

        if ($count >= 5){
            $this->cache->set($this->searchCacheKey, 1, getExpireTime());
        }
    }

    /**
     * 获取信息
     */
    public function userInfo()
    {
        $this->curl->setCookieString($this->config['cookie']);
        $data = $this->curl->get($this->config['userInfoUrl']);
        $data = stdObjectToArray($data);

        if (empty($data['data']['cash']['amount'])){
            sendEmail('头条cookie失效','头条cookie失效，请重置！');
        }

        info($this->index.'头条目前金额'.$data['data']['cash']['amount']);

        if ($data['data']['cash']['amount'] >= 15){
            sendEmail('头条该体现了！','头条体现了，金额'.$data['data']['cash']['amount']);
        }
    }

}