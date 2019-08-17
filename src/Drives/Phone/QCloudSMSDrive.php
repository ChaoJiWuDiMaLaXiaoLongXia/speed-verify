<?php
/**
 * 腾讯短信.
 * User: ChaoJiWuDiMaLaXiaoLongXia@gmail.com
 * Date: 2019/4/11
 */
namespace ChaoJiWuDiMaLaXiaoLongXia\SpeedVerify\Drives\Phone;

use ChaoJiWuDiMaLaXiaoLongXia\SpeedVerify\Drives\BaseDrive;
use ChaoJiWuDiMaLaXiaoLongXia\SpeedVerify\Clients\ClientLog as Log;
use Qcloud\Sms\SmsSingleSender;

final class QCloudSMSDrive extends BaseDrive
{
    const DRIVE_NAME = 'QCloudSMS';
    const DRIVE_MODE = 'phone';

    /**
     * 发送短信
     * @return array
     */
    public static function send()
    {
        $template  = self::$config['templates'][self::$app->template] ?? self::$config['templates']['public'];
        $request   = [
            'PhoneNumbers' => self::$app->to_phone_number,
            'SignName'     => self::$app->sign_name,
            'TemplateId'   => $template,
            'code'         => self::$app->verify_code,
        ];

        if(true === self::phoneEnable())
        {
            try {
                $sender   = new SmsSingleSender(self::$config['appid'], self::$config['appkey']);
                if(!empty(self::$app->send_message)) {
                    // 普通单发:普通单发需明确指定内容，如果有多个签名，请在内容中以【】的方式添加到信息内容中，否则系统将使用默认签名。
                    $response = $sender->send(0, '86', $request['PhoneNumbers'], self::$app->send_message);
                }else{
                    // 指定模板ID单发短信
                    $response = $sender->sendWithParam('86', $request['PhoneNumbers'], $request['TemplateId'],
                        [$request['code']], $request['SignName'], '', '');
                }


                // 签名参数未提供或者为空时，会使用默认签名发送短信
            } catch(\Exception $e) {
                Log::error(self::DRIVE_NAME.' 通道发短信出错啦！错误信息:'. $e->getMessage());
                return self::output(500, $e->getMessage());
            }
        }else{
            $response = '{"result":0,"errmsg":"OK","ext":"","sid":"TEST:'.date('YmdHis').'","fee":1}';
        }
        $responseArray = json_decode($response, 1);

        return self::result_format($request, $responseArray, $response);
    }

    /**
     * 数据格式转换
     * @param array $request
     * @param $result
     * @param $response
     * @return array
     */
    public static function result_format(array $request, $result, $response)
    {
        $result['result'] === 0 ? $status = 10000 : $status = 20000;

        return self::output(
            $status,
            $result['errmsg'],
            [
                'mode'           => self::$app->mode,
                'drive'          => self::DRIVE_NAME,
                'drive_name'     => self::$config['drive_name'],
                'to_phone_number'=> self::$app->to_phone_number,
                'to_email'       => self::$app->to_email,
                'verify_code'    => self::$app->verify_code,
                'template'       => self::$app->template,
                'template_id'    => $request['TemplateId'],
                'result_sid'     => $result['sid']??'',
            ],
            $request,
            $response
        );
    }
}