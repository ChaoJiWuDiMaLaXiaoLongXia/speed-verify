# speed-verify 极速验证码Laravel5.8+插件
- 支持发送短信验证码及邮件验证码；
- 更多支持见配置文件说明。

### 支持短信供应商:
- [阿里云短信](https://www.aliyun.com/product/sms)
- [腾讯云短信](https://cloud.tencent.com/product/sms)
- [集合数据](https://www.juhe.cn/docs/api/id/54)
- [美联软通](http://www.5c.com.cn/solution-application/#duanxintongzhi)

- 邮件发送使用`swiftmailer/swiftmailer`


## 安装依赖
- 1.1、引入包
```
composer require chaojiwudimalaxiaolongxia/speed-verify
```
- 1.2、获取配置文件`config/speed-verify.php`:
```
php artisan vendor:publish
```

- 1.3、创建数据库表`speed_verify_log`：
```
php artisan migrate
```



## 快速上手
- 在使用之前，需要配置好`config/speed-verify.php`然后可参考示例使用。

## 返回`status`状态码说明：
- status=10000 表示成功，其它表示失败！失败原因见info描述。

## 使用示例:
- 1.1、快速发送注册验证码示例:
```
//发送短信
try {
    $response = (new SpeedVerify())->autoDrive('register')->send('13800138000', '123456');
    print_r($response);
} catch (Exception $e) {
    print_r($e->getMessage());
}

//指定使用腾讯云短信通道发短信
try {
    $response = (new SpeedVerify())->setDrive('QCloudSMS')->setTemplate('register')->send('13800138000', '123456');
    print_r($response);
} catch (Exception $e) {
    print_r($e->getMessage());
}

//发送邮件
try {
    $response = (new SpeedVerify())->autoDrive('register')->send('admin@example.com', '123456');
    print_r($response);
} catch (Exception $e) {
    print_r($e->getMessage());
}
```


- 返回结果： 
```
Array
(
    [status] => 10000 //返回状态码:10000=成功，其它为失败
    [info] => OK
    [data] => Array
        (
            [mode] => phone
            [drive] => AliYunSMS
            [drive_name] => 阿里云
            [to_phone_number] => 13800138000
            [to_email] => 
            [verify_code] => 123456
            [template] => register
            [template_id] => SMS_123456789
            [result_sid] => 9599E1CC-D45A-48F0-AFDF-EE63F6248E42
        )

    [request] => Array
        (
            [RegionId] => cn-hangzhou
            [SignName] => 小龙虾
            [PhoneNumbers] => 13800138000
            [TemplateCode] => SMS_123456789
            [TemplateParam] => {"code":"123456"}
        )

    [response] => {"Message":"OK","RequestId":"9599E1CC-D45A-48F0-AFDF-EE63F6248E42","BizId":"293655958655379219^0","Code":"OK"}
)
```


- 1.2、校验验证码示例:
```
try {
    $response = (new SpeedVerify())->setTemplate('register')->verifyCode('13800138000', '123456');
    print_r($response);
} catch (Exception $e) {
    print_r($e->getMessage());
}
```
- 返回结果： 
```
//返回正确验证码结果:
Array
(
    [status] => 10000
    [info] => 验证码正确
    [data] => Array
        (
        )

    [request] => Array
        (
        )

    [response] => 
)

//返回校验失败结果:
Array
(
    [status] => 20000
    [info] => 验证码已失效
    [data] => Array
        (
        )

    [request] => Array
        (
        )

    [response] => 
)

```

- 2.1、发送设备场景验证码：
```
try {
    $sms = new SpeedVerify();
    $sms->setDrive('AliYunSMS');
    $sms->setTemplate('scenario');
    $sms->setVerifyCode(['code' => '123456', 'equipment' => 'NO0123456']);
    $sms->setPhoneNumber('13800138000');
    $response = $sms->send();
    print_r($response);
} catch (Exception $e) {
    print_r($e->getMessage());
}
```

- 返回结果：
```

Array
(
    [status] => 10000
    [info] => OK
    [data] => Array
        (
            [mode] => phone
            [drive] => AliYunSMS
            [drive_name] => 阿里云
            [to_phone_number] => 13800138000
            [to_email] => 
            [verify_code] => Array
                (
                    [code] => 123456
                    [equipment] => NO0123456
                )

            [template] => scenario
            [template_id] => SMS_123456789
            [result_sid] => 9599E1CC-D45A-48F0-AFDF-EE63F6248E42
        )

    [request] => Array
        (
            [RegionId] => cn-hangzhou
            [SignName] => 小龙虾
            [PhoneNumbers] => 13800138000
            [TemplateCode] => SMS_123456789
            [TemplateParam] => {"code":"123456","equipment":"NO0123456"}
        )

    [response] => {"Message":"OK","RequestId":"9599E1CC-D45A-48F0-AFDF-EE63F6248E42","BizId":"293655958655379219^0","Code":"OK"}
)

```

- 2.2、校验设备场景验证码示例:
```
try {
    $response = (new SpeedVerify())->setTemplate('scenario')->verifyCode('13800138000', ['code' => 123456', 'equipment' => 'NO0123456']);
    print_r($response);
} catch (Exception $e) {
    print_r($e->getMessage());
}
```

- 返回结果：
```
Array
(
    [status] => 10000
    [info] => 验证码正确
    [data] => Array
        (
        )

    [request] => Array
        (
        )

    [response] => 
)

```


## 配置信息`config/speed-verify.php`：
```
<?php
/**
 * 信息配置
 *
 */
return [
    'code_length'  => 6, //自动生成验证码长度
    'code_type'    => 1, //自动生成验证码格式：1数字，2字母，3数字+字母
    'interval_time'=> 60, //间隔发送时间（秒）0为不限制
    'day_send_max' => 10, //同类型验证码日发送次数限制（次）0为不限制
    'code_timeout' => 300, //验证码有效期(秒)
    'sign_name'    => '(签名)',
    'mode'         => 'phone', //验证码默认发送模式 （phone:短信,email:邮箱）
    'code_verify'  => true, //启用验证码验证功能（true:启用验证功能,false:关闭验证功能）*关闭后需要自己开发验证码验证逻辑。

    'phone' => [
        'enable'  => true, //真实发送开关 true真实发送，false不发送
        'default' => 'AliYunSMS', //默认驱动通道
        'drives'  => [

            //阿里大鱼
            'AliYunSMS' => [
                'drive_name'    => '阿里云',
                'drive_state'   => true, //可用状态（false:不可用，true:可用）
                'key'           => '(accessKeyId)',
                'secrete'       => '(accessKeySecret)',
                'region_id'     => 'cn-hangzhou',

                'templates' => [
                    'public' => [   //通用模板
                        'template_code' => '(SMS_123456789)',
                        'params_list'   => ['code']
                    ],
                    'register' => [  //注册
                        'template_code' => '(SMS_123456789)',
                        'params_list'   => ['code']
                    ],
                    'login' => [   //登录
                        'template_code' => '(SMS_123456789)',
                        'params_list'   => ['code']
                    ],
                    'reset-pwd' => [   //修改密码
                        'template_code' => '(SMS_123456789)',
                        'params_list'   => ['code']
                    ],
                    'forgot-pwd' => [   //找回密码
                        'template_code' => '(SMS_123456789)',
                        'params_list'   => ['code']
                    ],
                    'scenario' => [ //发送设备场景验证码
                        'template_code' => '(SMS_123456789)',
                        'params_list'   => ['code','equipment'] //code:验证码 equipment:设备场景
                    ]
                    //... 更多模板
                ],
            ],

            //腾讯短信
            'QCloudSMS' => [
                'drive_name'   => '腾讯短信',
                'drive_state'  => true, //可用状态（false:不可用，true:可用）
                'appid'        => '(sdkappid)',
                'appkey'       => '(sdkappid对应的appkey)',
                'templates' => [
                    'public'     => '(TemplateId)', //通用模板
                    'register'   => '(TemplateId)', //注册
                    'login'      => '(TemplateId)', //登录
                    'reset-pwd'  => '(TemplateId)', //修改密码
                    'forgot-pwd' => '(TemplateId)', //找回密码
                    //... 更多模板
                ]
            ],

            //集合数据
            'JuHeSMS' => [
                'drive_name'   => '集合数据',
                'drive_state'  => true, //可用状态（false:不可用，true:可用）
                'key'          => '(key)',
                'tpl_value'    => '#code#=%s',
                'templates' => [
                    'public'     => '(tpl_id)', //通用模板
                    'register'   => '(tpl_id)', //注册
                    'login'      => '(tpl_id)', //登录
                    'reset-pwd'  => '(tpl_id)', //修改密码
                    'forgot-pwd' => '(tpl_id)', //找回密码
                    //... 更多模板
                ],
            ],

            //美联软通
            'MeiLianSMS' => [
                'drive_name'   => '美联软通',
                'drive_state'  => true, //可用状态（false:不可用，true:可用）
                'username'     => '(username)',
                'password_md5' => '(password_md5)',
                'apikey'       => '(apikey)',
                'templates' => [
                    //通用模板
                    'public'   => '您本次操作的验证码为：{code}，该验证码有效时间为5分钟，打死不要告诉别人。【{sign}】',
                    //注册
                    'register' => '注册验证码为：{code}，该验证码有效时间为5分钟，打死不要告诉别人。【{sign}】',

                    //自定义优惠券
                    'coupons' => '尊敬的顾客您好，本店活动赠送您满500减250现金抵扣优惠券:{code}，期待您的光临。【{sign}】',
                    //... 更多模板
                ],
            ],

        ],

    ],


    'email' => [
        'enable'  => true, //真实发送开关 true真实发送，false不发送
        'default' => 'Swiftmailer', //默认驱动通道
        'drives'  => [
            'Swiftmailer' => [
                'drive_name'   => 'QQ邮箱',
                'drive_state'  => true, //可用状态（false:不可用，true:可用）
                'host'         => 'smtp.qq.com',
                'port'         => 465,
                'username'     => '(username@qq.com)',
                'password'     => '(password)',
                'encryption'   => 'ssl',
                'templates' => [
                    'public' => [
                        'subject' => '【{sign}】操作验证码邮件',
                        'body'    => '您本次操作的验证码为：{code}，该验证码有效时间为5分钟，打死不要告诉别人。【{sign}】',
                    ],
                    'register' => [
                        'subject' => '【{sign}】注册验证码邮件',
                        'body'    => '您注册的验证码为：{code}，该验证码有效时间为5分钟，打死不要告诉别人。【{sign}】',
                    ],
                    'login' => [
                        'subject' => '【{sign}】登录验证码邮件',
                        'body'    => '您登录的验证码为：{code}，该验证码有效时间为5分钟，打死不要告诉别人。【{sign}】',
                    ],
                    'reset-pwd' => [
                        'subject' => '【{sign}】修改密码验证码邮件',
                        'body'    => '您修改密码的验证码为：{code}，该验证码有效时间为5分钟，打死不要告诉别人。【{sign}】',
                    ],
                    'forgot-pwd' => [
                        'subject' => '【{sign}】验证码邮件',
                        'body'    => '您找回密码的验证码为：{code}，该验证码有效时间为5分钟，打死不要告诉别人。【{sign}】',
                    ],
                    //自定义优惠券
                    'coupons' => [
                        'subject' => '【{sign}】满500减250现金抵扣优惠券',
                        'body'    => '尊敬的顾客您好，本店活动赠送您满500减250现金抵扣优惠券:{code}，期待您的光临。【{sign}】',
                    ],
                    //... 更多模板
                ]
            ],
        ],
    ],
];
```