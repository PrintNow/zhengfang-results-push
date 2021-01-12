<?php
/**
 *
 * User: chuwen
 * Date Time: 2020/7/2 16:59
 * Email: <chenwenzhou@aliyun.com>
 */


#--------------------------------------------#
//使用本程序前，请先配置 lib/config.php 相关设置
//使用本程序前，请先配置 lib/config.php 相关设置
//使用本程序前，请先配置 lib/config.php 相关设置
#--------------------------------------------#


include __DIR__ . "/lib/functions.php";
include __DIR__ . "/lib/WxPusher.php";
$config = include __DIR__ . "/lib/config.php";

//实例化 WxPusher
$WxPusher = new WxPusher($config['WxPusherConfig']['APP_TOKEN']);

//初始化相关 变量
$UID = '';
$JSESSIONID = '';
$AlwaysPush = 'false';

if (is_cli() && isset($argv)) {
    $param = getopt('U:S:P:');

    if (isset($param['U'])) {
        $UID = $param['U'];
    }
    if (isset($param['S'])) {
        $JSESSIONID = $param['S'];
    }
    if (isset($param['P'])) {
        $AlwaysPush = $param['P'];
    }
} else {
    //获取向谁推送成绩信息 UID
    $UID = get('UID', '');

    //获取在教务系统获取的 JSESSIONID 值
    //如果传入了此值，则代表更新 cookie
    $JSESSIONID = get('JSESSIONID', '');

    //不管你的成绩是否有变化，总是推送消息
    $AlwaysPush = get('AlwaysPush', 'false');
}


$userConfPath = __DIR__ . "/userConf/{$UID}.php";
$resultTmpPath = __DIR__ . "/resultTmp/{$UID}.php";

//如果不存在用户相关信息配置文件，就新建一个
if (!file_exists($userConfPath)) {
    if (empty($UID) || empty($JSESSIONID)) {
        die("UID 或 JSESSIONID 参数不能为空");
    }

    //新建用户相关信息配置文件
    create_or_update_conf($UID, $JSESSIONID);
}

if (empty($UID)) {
    die("UID 参数不能为空");
}

//配置信息文件且传入了 JSESSIONID
//表示要更新 JSESSIONID
if (!empty($JSESSIONID)) {
    //更新用户相关信息配置文件
    create_or_update_conf($UID, $JSESSIONID);
}


//引入用户相关信息配置
include $userConfPath;

//配置要向谁推送微信消息的 UID
$WxPusher->setUid(_UID_);

//查询成绩信息
$queryScore = json_decode(curlPost(0, $config['URL'], [
    'xnm' => $config['XNM'],//哪个学年。详见 lib/functions.php 第14行
    'xqm' => $config['XQM'],//哪个学期。//详见 lib/functions.php 第14行
    'queryModel.showCount' => 100,//展示多少个数据
    'queryModel.currentPage' => 1,//第几页
    'queryModel.sortOrder' => 'asc'//排序方式
], [
    'Cookie: JSESSIONID=' . _JSESSIONID_
]), true);


/**
 * 判断查询的数据是否为空
 */
if ($queryScore === null) {
    //判断内容是否已经是 guoqi
    //如果不是则将文件内容改成  guoqi，并且推送消息去提醒用户
    //如果是则只输出运行结果
    if (file_get_contents($resultTmpPath) !== "guoqi") {
        score_tmp($resultTmpPath, "guoqi");

        //推送微信消息提醒
        $WxPusher->sendMsg(1, "Session 已经过期，请及时更新");
    }

    die("Session 过期，请及时更新" . PHP_EOL);
}

$pScore = parseScore($queryScore);//解析查询的数据
$resultJson = json_encode($pScore);

if (!file_exists($resultTmpPath)) {
    score_tmp($resultTmpPath, time());
}

if (md5($resultJson) === md5(file_get_contents($resultTmpPath)) && $AlwaysPush === 'false') {
    die("成绩无变化，不推送信息" . PHP_EOL);
}


//缓存成绩信息
score_tmp($resultTmpPath, $resultJson);


/**
 * 拼接成绩信息
 * @return string
 */
$enMsg = function () use ($pScore, $config) {
    $t = '';
    $t .= chengji_title($config['XNM'], $config['XQM']) . PHP_EOL . PHP_EOL;//标题

    foreach ($pScore as $k => $v) {
        $t .= "{$v['kcmc']}：{$v['cj']}\n";
    }

    $t .= PHP_EOL . "数据更新时间：" . date("Y-m-d H:i:s");

    return $t;
};

//判断是否推送成功
$res = json_decode($WxPusher->sendMsg(1, $enMsg()), true);

if ($res['code'] === 1000) {
    die("成绩推送成功" . PHP_EOL);
}

die("成绩推送失败，原因：{$res['msg']}" . PHP_EOL);
