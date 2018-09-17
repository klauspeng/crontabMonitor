<?php

/**
 * 贝壳-天津二手房数据抓取
 * Project :crontabMonitor
 * User: Klaus
 * Date: 2018.04.03 21:24
 */

use think\Db;

class Ketj extends \Core\TaskBase
{

    private $signCacheKey = 'ke_tj_spider';

    public function run()
    {
        // 数据库配置信息设置（全局有效）
        Db::setConfig([
            // 数据库类型
            'type'     => 'mysql',
            // 服务器地址
            'hostname' => '127.0.0.1',
            // 数据库名
            'database' => 'test',
            // 数据库用户名
            'username' => 'hrd_test',
            // 数据库密码
            'password' => 'hrd_test',
            // 数据库连接端口
            'hostport' => '3308',
            // 数据库连接参数
            'params'   => [],
            // 数据库编码默认采用utf8
            'charset'  => 'utf8',
            // 数据库表前缀
            'prefix'   => '',
        ]);

        // 判断今天是抓取
        // if (!$this->cache->get($this->signCacheKey)) {
        $this->getList();
        // }

    }

    /**
     * 抓取
     */
    public function getList()
    {
        // 配置
        $ershoufangLink = $this->config['ershoufangLink'];

        for ($i = 1; $i <= 100; $i++) {
            // 获取二手房首页
            $html     = file_get_contents($ershoufangLink . "pg{$i}/");
            $crawler  = new \Symfony\Component\DomCrawler\Crawler($html);
            $pageList = $crawler->filter('div.leftContent .sellListContent li.clear')
                ->each(function ($node, $i) {
                    // 关注信息
                    $followInfo = $node->filter('.followInfo')->text();
                    $followInfo = explode('/', $followInfo);

                    $link = $node->filter('.title a')->attr('href');

                    $date = date('Ymd');

                    // 详细页面
                    $detail = new \Symfony\Component\DomCrawler\Crawler(file_get_contents($link));

                    return [
                        // 房源ID
                        'hid'     => $node->filter('.unitPrice')->attr('data-hid'),
                        // 日期
                        'date'    => $date,
                        // 标题
                        'title'   => $node->filter('.title a')->text(),

                        // 单价
                        'price'   => $node->filter('.unitPrice')->attr('data-price'),
                        // 面积
                        'acreage' => str_replace('平米', '', $detail->filter('.houseInfo .area div.mainInfo')->text()),
                        // 总价
                        'amount'  => $node->filter('.priceInfo .totalPrice span')->text(),

                        'link'       => $link,

                        // 关注人数
                        'focus'      => trim($followInfo[0]),
                        // 带看
                        'tosee'      => trim($followInfo[1]),
                        // 发布
                        'publish'    => trim($followInfo[2]),

                        // 厅室
                        'room'       => $detail->filter('.houseInfo .room div.mainInfo')->text(),
                        // 朝向
                        'direction'  => $detail->filter('.houseInfo .type div.mainInfo')->text(),

                        // 行政区
                        'district'   => $detail->filter('.aroundInfo .areaName .info a')->first()->text(),
                        // 街道
                        'street'     => $detail->filter('.aroundInfo .areaName .info a')->last()->text(),
                        // 社区
                        'community'  => $detail->filter('.aroundInfo .communityName a.info')->text(),

                        // 楼层
                        'label'      => $detail->filter('.houseInfo .room div.subInfo')->text(),

                        // 电梯
                        'lift'       => str_replace('配备电梯', '', $detail->filter('#introduction .content li')->eq(11)->text()),

                        // 装修
                        'decoration' => str_replace('装修情况', '', $detail->filter('#introduction .content li')->eq(8)->text()),

                        // 梯户比例
                        'household'  => str_replace('梯户比例', '', $detail->filter('#introduction .content li')->eq(9)->text()),

                        // 供暖方式
                        'heating'    => str_replace('供暖方式', '', $detail->filter('#introduction .content li')->eq(10)->text()),

                        // 产权年限
                        'property'   => str_replace('产权年限', '', $detail->filter('#introduction .content li')->eq(12)->text()),

                    ];
                });

            // 进行CURD操作
            Db::table('ke_tj')->insertAll($pageList);
            echo date('H:i:s'),"第{$i}页查询成功！" . PHP_EOL;
        }

    }

}
