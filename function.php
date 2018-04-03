<?php
/**
 * Project :crontabMonitor
 * 公共函数
 * User: Klaus
 * Date: 2018.04.03 21:20
 */

/**
 * 记录日志
 * @param mixed $content 内容，可字符串，一维数组，二维数组
 * @param string $level 级别
 */
function info($content, $level = 'info')
{
    $time = date('Y-m-d H:i:s ') . $level . ' ';
    if (is_array($content)) {
        foreach ($content as $item) {
            if (is_array($item)) {
                echo $time . json_encode($item, JSON_UNESCAPED_UNICODE) . PHP_EOL;
            } else {
                echo $time . json_encode($content, JSON_UNESCAPED_UNICODE) . PHP_EOL;
                break;
            }
        }
    } else {
        echo $time . $content . PHP_EOL;
    }
}

