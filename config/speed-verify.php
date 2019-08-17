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