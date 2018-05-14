# crontabMonitor
每日签到，要分析登陆及签到接口;

定时抓取分析页面，定位数据，设置阈值监控；

邮件提醒，可发送到139等邮箱，进而免费实现短信提醒；

## 起因
自己投资些P2P，有家最后一笔投资奖励现金券5元，所以想做个监控下，在可投资范围内，随时准备投资，哈哈

## 借助工具
使用的插件如下：
1. symfony/dom-crawler
2. symfony/css-selector
3. phpmailer/phpmailer
4. thinkphp-FileDriver

## 使用说明
1. 需config.php.default 改为config.php，配置好信息
2. 根目录创建caches文件夹，并可写入
3. 定时设置：`*/1 * * * * /path/to/php /path/to/crontabMonitor/index.php >> /path/to/log/crontabMonitor.log`
4. 也可单次执行任务 `/path/to/php /path/to/crontabMonitor/index.php -t hrd`