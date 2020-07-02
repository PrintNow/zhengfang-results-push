<?php
/**
 *
 * User: chuwen
 * Date Time: 2020/7/2 17:05
 * Email: <chenwenzhou@aliyun.com>
 */


/**
 * SERVER CHAN 微信推送
 * @param string $text 消息标题，最长为256，必填。
 * @param string $desp 消息内容，最长64Kb，可空，支持MarkDown。
 * @param string $key 获取方式：http://sc.ftqq.com/?c=code
 * @return false|string
 */
function sc_send($text = '', $desp = '', $key = '')
{
    $postdata = http_build_query(
        array(
            'text' => $text,
            'desp' => $desp
        )
    );

    $opts = array('http' =>
        array(
            'method' => 'POST',
            'timeout' => 5,//超时时间 5秒
            'header' => 'Content-type: application/x-www-form-urlencoded',
            'content' => $postdata
        )
    );
    $context = stream_context_create($opts);

    $result = json_decode(@file_get_contents('https://sc.ftqq.com/' . $key . '.send', false, $context), true);
    return $result;
}

/**
 * 判断是否为命令行模式
 * @return bool
 */
function is_cli()
{
    return preg_match("/cli/i", php_sapi_name()) ? true : false;
}

/**
 * 获取 GET 参数助手
 * @param $parameter
 * @param null $default     如果不存在，默认值
 * @param string $filter    “过滤器”
 * @return string|array
 * @author Chuwen<wenzhouchan@gmail.com>
 */
function get($parameter, $default = null, $filter = 'trim')
{
    return isset($_GET[$parameter]) ? $filter($_GET[$parameter]) : $default;
}

/**
 * 获取 POST 参数助手
 * @param $parameter
 * @param null $default     如果不存在，默认值
 * @param string $filter    “过滤器”
 * @return string|array
 * @author Chuwen<wenzhouchan@gmail.com>
 */
function post($parameter, $default = null, $filter = 'trim')
{
    return isset($_POST[$parameter]) ? $filter($_POST[$parameter]) : $default;
}