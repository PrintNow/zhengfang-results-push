<?php
/**
 * 运行所有任务
 * User: chuwen
 * Date Time: 2020/7/2 18:14
 * Email: <chenwenzhou@aliyun.com>
 */

//TODO: 未来会做的

die;
include __DIR__ . "/lib/functions.php";
include __DIR__ . "/lib/WxPusher.php";
$config = include __DIR__ . "/lib/config.php";

if(!is_cli()){
    die("运行所有任务是比较耗时的操作，所以仅能在 CLI(命令行) 模式下运行".PHP_EOL);
}

//请先配置  Lib/config.php  第14行 APP_TOKEN
$WxPusher = new WxPusher($config['WxPusherConfig']['APP_TOKEN']);

$resultTmp = __DIR__."/resultTmp";
$userConf = __DIR__."/userConf";


$jobs = fileShow($userConf);

foreach ($jobs as $v){
    $_uc = include "{$userConf}/$v";

    $userConfPath = $userConf . "/"._UID_.".php";
    $resultTmpPath = $resultTmp . "/"._UID_.".php";

    //配置要向谁推送微信消息的 UID
    $WxPusher->setUid(_UID_);

    //查询成绩信息
    $queryScore = json_decode(curlPost(0, ResultQueryAPI, [
        'xnm' => XNM,//详见 lib/functions.php 第14行
        'xqm' => XQM,//3 上学期  12 下学期
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

        echo ("Session 过期，请及时更新" . PHP_EOL);
        continue;
    }

    $pScore = parseScore($queryScore);//解析查询的数据
    $resultJson = json_encode($pScore);


    if (md5($resultJson) === md5(file_get_contents($resultTmpPath)) && $AlwaysPush==='false') {
        echo ("成绩无变化，不推送信息" . PHP_EOL);
        continue;
    } else {
        //缓存成绩信息
        score_tmp($resultTmpPath, $resultJson);

        $enmsg = function () use ($pScore) {
            $t = '';
            $t .= chengji_title(XNM, XQM) . PHP_EOL . PHP_EOL;//标题

            foreach ($pScore as $k => $v) {
                $t .= "{$v['kcmc']}：{$v['cj']}\n";
            }

            $t .= PHP_EOL . "数据更新时间：" . date("Y-m-d H:i:s");

            return $t;
        };

        $res = json_decode($WxPusher->sendMsg(1, $enmsg()), true);

        if ($res['code'] === 1000) {
            echo ("成绩推送成功" . PHP_EOL);
        } else {
            echo ("成绩推送失败，原因：{$res['msg']}" . PHP_EOL);
        }
    }

    unset($_uc);
}