<?php

/**
 * Project :crontabMonitor
 * User: Klaus
 * Date: 2018.04.03 21:24
 */
class Hrd extends \Core\TaskBase
{
    public function run()
    {
        $this->getList();
    }

    /**
     * 获取列表
     */
    public function getList()
    {
        $list = [];
        $links = $this->config['listLink'];
        $mailList = [];

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

        foreach ($list as $item) {
            if (!$this->cache->has('hrd_' . $item['id']) && $item['money'] <= $this->config['money']) {
                $mailList[] = $item;
                $this->cache->set('hrd_' . $item['id'], $item, 3600);
            }
        }

        if (!empty($mailList)) {
            info($mailList);
        } else {
            info('no invest!');
        }
    }
}