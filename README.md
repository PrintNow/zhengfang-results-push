# 新版正方教务系统，成绩主动推送到微信

新版正方教务系统 期末成绩主动推送至微信等

# 配置方法

配置 `lib/config.php` 相关文件

> 你看注释应该可以看懂需要怎么更改，WXPusher 的使用，请继续往下看本文

```php
return [
    //如果你的教务系统强制使用  https  请将 http 末尾加一个 s
    //不知道 gnmkdm
    'URL' => 'http://教务系统域名/jwglxt/cjcx/cjcx_cxDgXscj.html?doType=query&gnmkdm=N305005',
    //请将“教务系统域名”改成你的正方教务系统域名

    //哪个学年的成绩
    //2020 表示 2020~2021学年度
    'XNM' => 2020,

    //3：上学期
    //12：下学期
    'XQM' => 3,

    //微信消息推送服务（由 WxPusher 提供）
    'WxPusherConfig' => [
        //你在 http://wxpusher.zjiecode.com/admin/app/list
        //创建的 APP_TOKEN
        'APP_TOKEN' => '',
    ]
];
```

# 关于 WxPusher 的使用

1. 请前往 [http://wxpusher.zjiecode.com/admin/app/list](http://wxpusher.zjiecode.com/admin/app/list) 新建一个应用
   > 没有注册账号先根据提示注册账号
   >
   > 除了带有 `*` 标记的表单需要填写，其它都不需要填。傻瓜式填写，不多讲述
2. 新建完应用你会获得一个 **APP_TOKEN** 复制它，将它填到 `config.php` 第14行
3. 然后不出意外会弹出一个二维码的模态框（如果没有，点击对应 应用的 **关注** 即可出现）
   ![1.png](https://i.loli.net/2021/01/12/9wCpExUluqn7I5V.png)
4. 扫码关注公众号（这个二维码可以保存下来）
5. 然后点击下方的 我的 -> 我的UID 获取UID，格式是 `UID_xxxxxxxxx`
   ![2.png](https://i.loli.net/2021/01/12/HZaVpP3Lne6zEFb.png)

# 获取教务系统 JSESSIONID

1. 打开教务系统
2. 点击如图

   ![3.jpg](https://i.loli.net/2021/01/12/mXGxTM84FKRLqBN.jpg)
3. 复制 JSESSIONID
   ![4.png](https://i.loli.net/2021/01/12/H6YfvAIw3kb29zi.png)
   ![5.png](https://i.loli.net/2021/01/12/YoX5aRK6DjnIF4Z.png)

# 使用方法

1. **首次查询**成绩并推送（第一次添加或更新 JSESSIONID 时用这种方法）
    1. 命令行方法，执行 `php main.php -U "你的UID" -S "获取的 JSESSIONID"`
    2. 网页方法，访问 `http://你的域名/main.php?UID=你的UID&JSESSIONID=获取的SESSION`

2. **首次查询成功之后**，因为已经缓存了 JSESSIONID，所以不需要传 JSESSIONID 参数，可直接传入 `U` 参数即可
   > 对于监控，你可以使用如 crontab 命令进行定时执行脚本，如果你不会使用，请上网搜索关键字 **Linux crontab 定时执行命令**
   > >
   > 我们学校教务系统大概**每2分钟执行一次**，`JSESSIONID` 就不会失效，你们学校的教务系统，具体情况具体更改
    1. 命令行方法，执行 `php main.php -U "你的UID"`
    2. 网页方法，访问 `http://你的域名/main.php?UID=你的UID`

# 运行结果

![新版正方教务系统 成绩主动查询 微信推送运行结果](https://i.loli.net/2021/01/12/k4OfriNGUB26gdL.jpg)

[http://wxpusher.zjiecode.com/admin/app/list]: http://wxpusher.zjiecode.com/admin/app/list