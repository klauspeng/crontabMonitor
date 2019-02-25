<?php

use Core\TaskBase;

/**
 * Desc : Gold 上海黄金交易所数据监控
 * User : liupeng
 * Date : 2019-02-19
 */
class Gold extends TaskBase
{
    private $minGoldPrice = 260;
    private $goldCacheKey = 'gold_info';

    public function run()
    {
        $this->getData();
    }


    /**
     * 获取黄金数据
     */
    public function getData()
    {
        // 判断今天是否获取
        if ($this->cache->get($this->goldCacheKey)) {
            return FALSE;
        }

        $info = $this->curl->get($this->config['infoUrl']);

        $goldDataCrawler = new \Symfony\Component\DomCrawler\Crawler($info);
        $goldPrice       = $goldDataCrawler->filter('#myinstid')->parents()->children()->eq(1)->text();
        info('黄金价格：',$goldPrice);

        if ($goldPrice && $goldPrice <= $this->minGoldPrice) {
            sendEmail('可以买黄金了_' . $goldPrice, '可以买黄金了。当前价格' . $goldPrice);
        }

        $this->cache->set($this->goldCacheKey, 1, getExpireTime(9));
    }

}