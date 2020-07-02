<?php
/**
 *
 * User: chuwen
 * Date Time: 2020/7/2 16:47
 * Email: <chenwenzhou@aliyun.com>
 * 请参照开发文档操作 http://wxpusher.zjiecode.com/
 */

class WxPusher
{
    //WxPusher 的 API
    const POST_API = 'http://wxpusher.zjiecode.com/api/send/message';
//    const APP_TOKEN = 'AT_L9suU3pFmeBLK4WfhPFt6msvIfEpGCj3';

    //需要向谁推送的 UID
    private $uid = "";

    //你在 http://wxpusher.zjiecode.com/admin/app/list
    //创建的 APP_TOKEN
    private $app_token = "";


    public function __construct($app_token="")
    {
        $this->app_token = $app_token;
    }


    /**
     * @param string $uid 需要向谁推送的 UID
     */
    public function setUid($uid = "")
    {
        $this->uid = $uid;
    }


    /**
     * 发送信息
     * @param int $contentType
     * @param string $msg
     * @return bool|string
     */
    public function sendMsg($contentType = 3, $msg = "")
    {
        return $this->urlPost(1, self::POST_API, [
            'appToken' => $this->app_token,
            'content' => $msg,
            'contentType' => $contentType,
            'uids' => [
                $this->uid
            ]
        ], [
            'Content-Type: application/json; charset=utf-8'
        ]);
    }


    /**
     * HTTP POST
     * @param int       $postType   POST 数据类型，1：json方式推送，其它数字：默认方式推送
     * @param string    $url        需要 POST 数据的 URL
     * @param array     $data       需要 POST 的数据
     * @param array     $headers    需要请求时携带的 headers，不传请留空
     * @param int       $timeout    超时时间，默认5秒
     * @return bool|string
     */
    public function urlPost($postType = 1, $url = "", $data = [], $headers = [], $timeout = 5)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);

        curl_setopt($curl, CURLOPT_POST, true);
        if ($postType === 1) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        } else {
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
        }
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $res = curl_exec($curl);

        //$info = curl_getinfo($ch);
        curl_close($curl);

        return $res;
    }
}