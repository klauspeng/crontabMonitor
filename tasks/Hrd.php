<?php

/**
 * Project :crontabMonitor
 * User: Klaus
 * Date: 2018.04.03 21:24
 */
class Hrd extends \Core\TaskBase
{
    private $signCacheKey = 'hrd_sign';

    public function run()
    {
        // 获取1-3月列表
        $this->getList();

        // 登陆
        $this->sign();
    }

    /**
     * 获取列表
     */
    public function getList()
    {
        $list = [];
        // 配置
        $links = $this->config['listLink'];
        // 可投列表并邮件
        $mailList = [];

        // 循环读取列表页
        foreach ($links as $link) {
            $html = file_get_contents($link);
            $crawler = new \Symfony\Component\DomCrawler\Crawler($html);
            $pageList = $crawler->filter('.invest-item:not(.newbie)')
                ->each(function ($node, $i) {
                    return [
                        'id'    => $node->filter('.submit a')->attr('data-count-key'),
                        'month' => $node->filter('.child strong')->text(),
                        'money' => $node->filter('.last strong')->text(),
                        'link'  => 'https://www.huirendai.cn' . $node->filter('.submit a')->attr('data-modal-url'),
                    ];
                });
            $pageList && $list = array_merge($list, $pageList);
        }

        // 筛选可投的项目
        foreach ($list as $item) {
            if (!$this->cache->has('hrd_' . $item['id']) && $item['money'] <= $this->config['money'] && $item['money'] > 0) {
                $mailList[] = $item;
                $this->cache->set('hrd_' . $item['id'], $item, 3600);
            }
        }

        // 如有可投则邮件通知
        if (!empty($mailList)) {
            info('time to invest !', $mailList);
            sendEmail('有可投标的！(' . $mailList[0]['month'] . '月标-' . $mailList[0]['money'] . ')', json_encode($mailList, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        } else {
            info('no invest!', $list);
        }
    }

    /**
     * 签到
     */
    public function sign()
    {
        $cookieSuccess = CACHE_PATH . "hrd_cookie.tmp";

        // 判断今天是否签到
        if ($this->cache->get($this->signCacheKey)) {
            return false;
        }

        // 登陆
        $this->login($cookieSuccess);

        // 去签到
        $this->signIn($cookieSuccess);
    }

    /**
     * 登陆并保存cookie
     * @param string $cookieSuccess cookie保存文件
     *
     * @return mixed
     */
    public function login($cookieSuccess)
    {
        // 设置保存cookie文件 POST请求
        $this->curl->setCookieJar($cookieSuccess);
        $this->curl->post($this->config['loginUrl'], http_build_query($this->config['loginData']));
        if ($this->curl->error) {
            info('请求失败:', $this->curl->errorCode . ': ' . $this->curl->errorMessage);
            return FALSE;
        }
        $result = json_decode($this->curl->response, TRUE);

        info('惠人贷登陆结果：', $result);

    }

    /**
     * 签到
     */
    public function signIn($cookieSuccess)
    {
        // 使用登陆获取的cookies POST请求
        $this->curl->setCookieFile($cookieSuccess);
        $this->curl->post($this->config['singUrl'], http_build_query(['action' => 'signin', 'theday' => 7]));
        $data = json_decode($this->curl->response, TRUE);
        if ($data['code'] === '00000' || $data['code'] === '20009') {
            // 缓存
            $this->cache->set($this->signCacheKey, 1, getExpireTime());

            // 提醒领签到礼包
            if (isset($data['info']) && end($data['info']) == 3) {
                sendEmail('领签到礼包了！', '领签到礼包了！');
            }
        }

        info('惠人贷签到结果：', $data);
    }
}
