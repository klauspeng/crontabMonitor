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

        if (!empty($mailList)) {
            info('time to invest !', $mailList);
            sendEmail('18211089602@139.com','有可投标的！',json_encode($mailList,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
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

        // 获取登陆信息
        $this->sginInfo($cookieSuccess);

        // 去签到
        $this->signIn($cookieSuccess);
    }

    /**
     * 登陆并保存cookie
     * @param string $cookieSuccess cookie保存文件
     */
    public function login($cookieSuccess)
    {
        $ch = curl_init();
        // 返回结果存放在变量中，不输出
        curl_setopt($ch, CURLOPT_URL, $this->config['loginUrl']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 120);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($this->config['loginData']));
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieSuccess);//用来存放登录成功的cookie
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $result = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($result, true);
        info('登陆结果：', $result);
    }

    /**
     * 获取签到信息
     * @param string $cookieSuccess 保存cookie文件
     */
    public function sginInfo($cookieSuccess)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->config['singUrl']);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['action' => 'signinfo']));
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieSuccess); //使用上面获取的cookies
        $data = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($data, true);
        info('签到信息：', $data);
    }

    /**
     * 签到
     */
    public function signIn($cookieSuccess)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->config['singUrl']);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['action' => 'signin', 'theday' => 7]));
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieSuccess); //使用上面获取的cookies
        $data = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($data, true);
        if ($data['code'] === '00000') {
            $this->cache->set($this->signCacheKey, 1, getExpireTime());
        }
        info('签到结果：', $data);
    }
}
