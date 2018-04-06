<?php
/**
 * Project :crontabMonitor
 * 公共函数
 * User: Klaus
 * Date: 2018.04.03 21:20
 */

/**
 * 记录日志
 * @param string $info 信息
 * @param array $content 内容，可字符串，一维数组，二维数组
 * @param string $level 级别
 */
function info($info, $content = [], $level = 'info')
{
    $time = date('Y-m-d H:i:s ') . $level . ' ';
    echo $time . $info . PHP_EOL;
    if ($content && is_array($content)) {
        foreach ($content as $item) {
            if (is_array($item)) {
                echo json_encode($item, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;
            } else {
                echo json_encode($content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;
                break;
            }
        }
    }
}

