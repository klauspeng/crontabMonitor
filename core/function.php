<?php
/**
 * Project :crontabMonitor
 * 公共函数
 * User: Klaus
 * Date: 2018.04.03 21:20
 */

/**
 * 记录日志
 *
 * @param string $info    信息
 * @param array  $content 内容，可字符串，一维数组，二维数组
 * @param string $level   级别
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
    } else {
        echo $content;
    }
}


function sendEmail($email, $title, $content)
{
    global $configs;
    $mail = new \PHPMailer\PHPMailer\PHPMailer();
    $mail->Charset='UTF-8';
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
        $mail->addAddress($email);               // Name is optional

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

