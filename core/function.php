<?php
/**
 * Project :crontabMonitor
 * 公共函数
 * User: Klaus
 * Date: 2018.04.03 21:20
 */

/**
 * 记录日志
 * @param string $info    信息
 * @param array  $content 内容，可字符串，一维数组，二维数组
 * @param string $level   级别
 */
function info($info, $content = [], $level = 'info')
{
    $time = date('Y-m-d H:i:s ') . $level . ' ';
    echo $time . $info;
    if ($content && is_array($content)) {
        foreach ($content as $item) {
            if (is_array($item)) {
                echo json_encode($item, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;
            } else {
                echo json_encode($content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;
                break;
            }
        }
    } else if (!empty($content)) {
        echo $content . PHP_EOL;
    } else {
        echo PHP_EOL;
    }
}


function sendEmail($title, $content, $email = '')
{
    global $configs;
    $mail          = new \PHPMailer\PHPMailer\PHPMailer();
    $mail->Charset = 'UTF-8';
    try {
        //Server settings
        // $mail->SMTPDebug = 2;                                 // Enable verbose debug output
        $mail->isSMTP();                                      // Set mailer to use SMTP
        $mail->Host       = $configs['mail']['host'];  // Specify main and backup SMTP servers
        $mail->SMTPAuth   = TRUE;                               // Enable SMTP authentication
        $mail->Username   = $configs['mail']['userName'];                 // SMTP username
        $mail->Password   = $configs['mail']['password'];                           // SMTP password
        $mail->SMTPSecure = 'ssl';                            // Enable TLS encryption, `ssl` also accepted
        $mail->Port       = $configs['mail']['port'];                                    // TCP port to connect to

        //Recipients
        $mail->setFrom($configs['mail']['userName'], 'Mailer');

        if ($email) {
            $mail->addAddress($email);
        } else {
            $mail->addAddress($configs['mail']['addressee']);
        }

        //Content
        $mail->isHTML(TRUE);                                  // Set email format to HTML
        $mail->Subject = '=?utf-8?B?' . base64_encode($title) . '?=';
        $mail->Body    = $content;

        $mail->send();
        return TRUE;
    } catch (Exception $e) {
        info('Message could not be sent. Mailer Error: ' . $mail->ErrorInfo);
        return FALSE;
    }
}

/**
 * 计算当前时间到明天00:00的秒数
 * @param int $hour 每天几点,默认7点
 * @return  mixed
 */
function getExpireTime($hour = 7)
{
    $tomorrow = strtotime(date('Y-m-d', strtotime('+1 day')));
    return $tomorrow + $hour * 3600 - time();
}

/**
 * stdClass Object转array
 * @param object $stdObject
 * @return array
 */
function stdObjectToArray($stdObject)
{
    return json_decode(json_encode($stdObject), TRUE);
}

/**
 * 随机生成国内ip
 * @return string
 */
function fakeIp()
{
    $ip_long  = array(
        array('607649792', '608174079'), // 36.56.0.0-36.63.255.255
        array('1038614528', '1039007743'), // 61.232.0.0-61.237.255.255
        array('1783627776', '1784676351'), // 106.80.0.0-106.95.255.255
        array('2035023872', '2035154943'), // 121.76.0.0-121.77.255.255
        array('2078801920', '2079064063'), // 123.232.0.0-123.235.255.255
        array('-1950089216', '-1948778497'), // 139.196.0.0-139.215.255.255
        array('-1425539072', '-1425014785'), // 171.8.0.0-171.15.255.255
        array('-1236271104', '-1235419137'), // 182.80.0.0-182.92.255.255
        array('-770113536', '-768606209'), // 210.25.0.0-210.47.255.255
        array('-569376768', '-564133889'), // 222.16.0.0-222.95.255.255
    );
    $rand_key = mt_rand(0, 9);
    return long2ip(mt_rand($ip_long[$rand_key][0], $ip_long[$rand_key][1]));
}

/**
 * 获取当前毫秒级时间戳
 * @return float
 */
function getMillisecond()
{
    list($t1, $t2) = explode(' ', microtime());
    return (float) sprintf('%.0f', (floatval($t1) + floatval($t2)) * 1000);
}