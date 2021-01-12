<?php
/**
 *
 * User: chuwen
 * Date Time: 2020/7/2 17:05
 * Email: <chenwenzhou@aliyun.com>
 */

/**
 * 展示指定路径下的所有文件
 * @param string $dir 展示哪个路径下的文件
 * @param array $ignoreFile 忽略的文件，是一个数组
 * @return array
 */
function fileShow($dir = './', $ignoreFile = [])
{
    $handle = opendir($dir);
    $arr = [];

    while ($file = readdir($handle)) {
        if ($file === '..' || $file === '.') continue;

        //如果该文件存在于排除名单
        //则跳过本次循环
        if (in_array($file, $ignoreFile)) continue;

        $f = $dir . '/' . $file;

        //判断是否为文件
        if (is_file($f)) {
            //是的话压入数组
            array_push($arr, $file);
        }
    }

    return $arr;
}

/**
 * 成绩标题
 * @param int $XN 哪个学年
 * @param int $XQ 哪个学期
 * @return string
 */
function chengji_title($XN, $XQ)
{
    $XN = intval($XN);
    $XQ = intval($XQ);

    return sprintf("[期末成绩信息更新通知]\n%d-%d学年，第%d学期",
        $XN, $XN + 1, [3 => 1, 12 => 2][$XQ]
    );
}

/**
 * 解析 新版正方教务系统 查询到的成绩信息
 * @param array $arr
 * @return  array
 */
function parseScore($arr = [])
{
    $data = [];
    foreach ($arr['items'] as $k => $v) {
        $data[] = [
            'kcmc' => $v['kcmc'],//课程名称
            'ksxz' => $v['ksxz'],//考试性质
            'cj' => $v['cj'],//成绩
            'jd' => $v['jd'],//绩点
            'xf' => $v['xf'],//学分
        ];
    }

    return $data;
}

/**
 * 创建缓存文件
 * @param $path
 * @param $data
 */
function score_tmp($path, $data)
{
    file_put_contents($path, $data) or die("无文件写入权限，请检查 userConf 文件夹权限" . PHP_EOL);
}


/**
 * HTTP POST
 * @param int $postType POST 数据类型，1：json方式推送，其它数字：默认方式推送
 * @param string $url 需要 POST 数据的 URL
 * @param array $data 需要 POST 的数据
 * @param array $headers 需要请求时携带的 headers，不传请留空
 * @param int $timeout 超时时间，默认5秒
 * @return bool|string
 */
function curlPost($postType = 1, $url = "", $data = [], $headers = [], $timeout = 5)
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

/**
 * 创建或更新 用户配置文件
 * @param $UID
 * @param $JSESSIONID
 */
function create_or_update_conf($UID, $JSESSIONID)
{
    $uCTmp = file_get_contents(dirname(__DIR__) . "/userConf/template.php");
    $uCTmp = str_replace([
        '{{_UID_}}',
        '{{_JSESSIONID_}}'
    ], [
        '{{_UID_}}' => $UID,
        '{{_JSESSIONID_}}' => $JSESSIONID
    ], $uCTmp);

    //新建用户相关信息配置文件
    file_put_contents(dirname(__DIR__) . "/userConf/{$UID}.php", $uCTmp) or die("无文件写入权限，请检查 userConf 文件夹权限" . PHP_EOL);
}

/**
 * SERVER CHAN 微信推送（本程序未使用，需要的话自己实现）
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
 * @param null $default 如果不存在，默认值
 * @param string $filter “过滤器”
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
 * @param null $default 如果不存在，默认值
 * @param string $filter “过滤器”
 * @return string|array
 * @author Chuwen<wenzhouchan@gmail.com>
 */
function post($parameter, $default = null, $filter = 'trim')
{
    return isset($_POST[$parameter]) ? $filter($_POST[$parameter]) : $default;
}