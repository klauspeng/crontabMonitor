<?php
/**
 * Project :crontabMonitor
 * User: Klaus
 * Date: 2018.04.03 21:18
 */

header("Content-Type: text/html;charset=utf-8");

// 定义常量
define('ROOT_PATH', __DIR__ . DIRECTORY_SEPARATOR);
define('TASKS_PATH', ROOT_PATH . 'tasks' . DIRECTORY_SEPARATOR);
define('CACHE_PATH', ROOT_PATH . 'caches' . DIRECTORY_SEPARATOR);

// 引入自动加载
include ROOT_PATH . 'vendor/autoload.php';

// 加载配置及公共函数
$configs = include ROOT_PATH . 'config.php';
include ROOT_PATH . '/core/function.php';

// 获取传入task
$param = getopt('t:');
isset($param['t']) && $file = TASKS_PATH . ucfirst($param['t']) . '.php';

// 执行任务
if (isset($file) && is_file($file)) {
    // 单任务执行
    require $file;
    $config = [];
    isset($configs[$param['t']]) && $config = $configs[$param['t']];
    $taskObject = new $param['t']($config);
    $taskObject->run();
} else {
    // 所有任务执行
    $tasks = array_diff(scandir(TASKS_PATH), array('..', '.'));
    foreach ($tasks as $task) {
        require TASKS_PATH . $task;
        $class = pathinfo($task, PATHINFO_FILENAME);
        if (!$class) {
            continue;
        }
        $config = isset($configs[lcfirst($class)]) ? $configs[lcfirst($class)] : [];
        $taskObject = new $class($config);
        $taskObject->run();
    }
}