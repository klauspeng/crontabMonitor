<?php
/**
 * Created by PhpStorm.
 * User: Liupeng
 * Date: 2018-05-22
 * Time: 12:46
 */

/**
 * 今日头条签到
 * Class Toutiao
 */
class Toutiao extends \Core\TaskBase
{
    private $signCacheKey = 'toutiao_sign';
    private $boxCacheKey = 'toutiao_hour_box';

    public function run()
    {
        // 签到
        $this->signIn();

        // 开启宝箱
        $this->openBox();
    }

    /**
     * 签到
     */
    public function signIn()
    {

        // 判断今天是否签到
        if ($this->cache->get($this->signCacheKey)) {
            return FALSE;
        }

        // 设置cookie POST请求
        $this->curl->setCookieString($this->config['cookie']);
        $this->curl->post($this->config['singUrl']);
        if ($this->curl->error) {
            info('请求失败:', $this->curl->errorCode . ': ' . $this->curl->errorMessage);
            return FALSE;
        }
        $data = stdObjectToArray($this->curl->response);

        // 校验是否成功
        if ($data['err_no'] != 0 && $data['err_no'] != 1025) {
            sendEmail('今日头条签到失败！', '今日头条签到失败！更换签到链接！');
        } else {
            $this->cache->set($this->signCacheKey, 1, getExpireTime());
        }

        info('今日头条签到结果：', $data);

    }

    /**
     * 开启宝箱
     */
    public function openBox()
    {

        // 判断今天是否签到
        if ($this->cache->get($this->boxCacheKey)) {
            return FALSE;
        }

        // 设置cookie POST请求
        $this->curl->setCookieString($this->config['cookie']);
        $this->curl->post($this->config['hourBoxUrl']);
        if ($this->curl->error) {
            info('请求失败:', $this->curl->errorCode . ': ' . $this->curl->errorMessage);
            return FALSE;
        }
        $data = stdObjectToArray($this->curl->response);

        // 校验是否成功
        if ($data['err_no'] == 0) {
            $this->cache->set($this->boxCacheKey, 1, $data['data']['next_treasure_time'] - time());
        }

        info('今日头条开宝箱结果：', $data);
    }

}