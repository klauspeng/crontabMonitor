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

    public function run()
    {
        $this->getData();
    }


    /**
     * 获取黄金数据
     */
    public function getData()
    {
        $info = $this->curl->get($this->config['infoUrl']);

        $goldDataCrawler = new \Symfony\Component\DomCrawler\Crawler($info);
        $goldPrice       = $goldDataCrawler->filter('#myinstid')->parents()->children()->eq(1)->text();

        if ($goldPrice <= $this->minGoldPrice) {
            sendEmail('可以买黄金了_' . $goldPrice, '可以买黄金了。当前价格' . $goldPrice);
        }
    }

}