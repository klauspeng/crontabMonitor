<?php

/**
 * 我爱我家-天津二手房数据抓取
 * Project :crontabMonitor
 * User: Klaus
 * Date: 2018.09.20 14:24
 */

use think\Db;

class Tj5i5j extends \Core\TaskBase
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
        $ershoufangLinks = $this->config['ershoufangLink'];
        $worker          = [];
        $desc            = [];
        $startTime       = time();

        foreach ($ershoufangLinks as $key => $ershoufangLink) {

            //创建多线程
            $pro = new swoole_process(function (swoole_process $work) use ($ershoufangLink, $key) {
                //获取html文件
                $this->getData($key, $ershoufangLink);

            }, TRUE);

            $pro_id        = $pro->start();
            $desc[$pro_id] = $key;
        }

        //子进程结束必须要执行wait进行回收，否则子进程会变成僵尸进程
        while ($ret = swoole_process::wait()) {// $ret 是个数组 code是进程退出状态码，
            $pid  = $ret['pid'];
            $time = (time() - $startTime) / 60;
            echo $desc[$pid] . '查询完毕！用时：' . $time . '分。' . PHP_EOL;
        }
    }

    public function getData($key, $ershoufangLink)
    {
        // $this->curl->setUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.89 Safari/537.36');
        // $this->curl->setReferer('https://tj.5i5j.com/ershoufang/nankaiqu/n2/');
        $this->curl->setCookieString('morCon=null; PHPSESSID=7h1j1m9q64v2u3am8krm1v3pha; _wjstatis=8c8e0515-2e12-f0e9-66df-db157b5c148c; yfx_c_g_u_id_10000001=_ck18090412054315837410231117185; ershoufang_cookiekey=%5B%22%257B%2522url%2522%253A%2522%252Fershoufang%252F_%2525E9%25259D%252593%2525E4%2525B8%25259C%2525E8%25258A%2525B1%2525E5%25259B%2525AD%253Fzn%253D%2525E9%25259D%252593%2525E4%2525B8%25259C%2525E8%25258A%2525B1%2525E5%25259B%2525AD%2522%252C%2522x%2522%253A%25220%2522%252C%2522y%2522%253A%25220%2522%252C%2522name%2522%253A%2522%25E9%259D%2593%25E4%25B8%259C%25E8%258A%25B1%25E5%259B%25AD%2522%252C%2522total%2522%253A%25220%2522%257D%22%5D; _Jo0OQK=3C411BD6D28A82D7281D05ACA2A69CA6144BCF9F8F87968DD40B4CF170AEC89972032E6F937B0BF8BF84A3C4E4B329BC2B13D15DF120F56705490FE808400E27B2A6E9FBA231F0A8926D79E59878EC30CCAD79E59878EC30CCA4A5A7839268EF3BADC3DC32B40538DC5GJ1Z1RA==; ershoufang_BROWSES=41218084%2C40915059%2C39371284; yfx_f_l_v_t_10000001=f_t_1536033943575__r_t_1537406369713__v_t_1537428888750__r_c_2; domain=tj; Hm_lvt_94ed3d23572054a86ed341d64b267ec6=1536033944,1537322270; Hm_lpvt_94ed3d23572054a86ed341d64b267ec6=1537429883');
        // 获取总页数
        do {
            $ershoufangLinkContent = $this->curl->get($ershoufangLink);
        } while (!$ershoufangLinkContent);

        $ershoufangLinkCrawler = new \Symfony\Component\DomCrawler\Crawler($ershoufangLinkContent);

        $totalPage = $ershoufangLinkCrawler->filter('.pListBox .lfBox .noBor span')->text();
        $totalPage = ceil($totalPage / 30);
        unset($ershoufangLinkCrawler);

        for ($i = 1; $i <= $totalPage; $i++) {
            // 获取二手房首页
            do {
                $html = $this->curl->get($ershoufangLink . "n{$i}/");
            } while (!$html);

            $crawler  = new \Symfony\Component\DomCrawler\Crawler($html);
            $pageList = $crawler->filter('div.list-con-box ul.pList li')
                ->each(function ($node, $i) use ($key) {
                    // 关注信息
                    $followInfo = $node->filter('.listX p')->eq(2)->text();
                    $followInfo = explode('·', $followInfo);

                    $link = 'https://tj.5i5j.com';
                    $link .= $node->filter('.listTit a')->attr('href');

                    $date = date('Ymd');

                    $detailInfo = $node->filter('.listX p')->first()->text();
                    $detailInfo = explode('·', $detailInfo);

                    $mapInfo = $node->filter('.listX p')->eq(1)->text();
                    $mapInfo = explode('·', $mapInfo);

                    return [
                        // 房源ID
                        'hid'        => str_replace('.html', '', @end(explode('/', $link))),
                        // 日期
                        'date'       => $date,
                        // 标题
                        'title'      => $node->filter('.listTit a')->text(),

                        // 单价
                        'price'      => str_replace(['单价', '元/m²'], '', $node->filter('.jia p')->last()->text()),
                        // 面积
                        'acreage'    => trim(str_replace('平米', '', $detailInfo[1])),
                        // 总价
                        'amount'     => $node->filter('.jia .redC strong')->text(),
                        // 链接
                        'link'       => $link,
                        // 关注人数
                        'focus'      => trim(str_replace('人关注', '', trim($followInfo[0]))),
                        // 带看
                        'tosee'      => trim(str_replace(['近30天带看', '次'], '', trim($followInfo[1]))),
                        // 发布
                        'publish'    => trim($followInfo[2]),
                        // 厅室
                        'room'       => trim($detailInfo[0]),
                        // 朝向
                        'direction'  => trim($detailInfo[2]),
                        // 行政区
                        'district'   => $key,
                        // 街道
                        'street'     => explode(' ', $mapInfo[0])[0],
                        // 社区
                        'community'  => @explode(' ', $mapInfo[0])[1],
                        // 楼层
                        'label'      => trim($detailInfo[3]),
                        // 电梯
                        'lift'       => '',
                        // 装修
                        'decoration' => @trim($detailInfo[4]),
                        // 梯户比例
                        'household'  => '',
                        // 供暖方式
                        'heating'    => '',
                        // 产权年限
                        'property'   => '',
                        // 来源
                        'source'     => '5i5j',

                    ];
                });

            // 进行CURD操作
            Db::table('ke_tj')->insertAll($pageList);
        }
    }

}
