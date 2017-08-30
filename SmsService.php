<?php

// +----------------------------------------------------------------------
// | Kunpeng游戏后台管理系统
// +----------------------------------------------------------------------
// | 版权所有 2017~2017 上海鲲佑科技有限公司 [  ]
// +----------------------------------------------------------------------
// | 官方网站:
// +----------------------------------------------------------------------

namespace service;

include_once __DIR__ . '/../sdk/aliyun-php-sdk-core/Config.php';
include_once __DIR__ . '/../sdk/Dybaseapi/Request/V20170525/SendSmsRequest.php';
include_once __DIR__ . '/../sdk/Dybaseapi/Request/V20170525/QuerySendDetailsRequest.php';

use DefaultProfile;
use DefaultAcsClient;
use Dysmsapi\Request\V20170525\SendSmsRequest;
use think\Db;

/**
 * 短信服务
 * Class SmsService
 * @package service
 * @author 杨亚军(Yang Yajun) <yyj4399@gmail.com>
 */
class SmsService {

    // 配置信息
    protected static $config = [
        'app_key'    => 'B9HicSvRojLFh3mR',
        'app_secret' => 'JP3UYkVt1W94f4J7l6TIg3bn57Tz0D',
        'sandbox'    => false,  // 是否为沙箱环境，默认false
    ];

    /**
     * 发送验证码
     * @param string $phone 手机号
     * @param string $signName 模板签名
     * @param string $templateCode 模板ID
     * @param string $code 验证码
     * @return array[状态, msg]
     */
    public static function sendCode($ip, $phone, $signName, $templateCode, $code = '') {
        empty($code) && $code = self::makeCode();
        list($db, $id) = self::addData($ip, $phone, $signName, $templateCode, $code);
        if ($id == false){
            return [1, 'db'];
        }
        // 发起
        $result = self::sendSms($db, $id, $phone, $signName, $templateCode, ['code' => $code]);
        if ($result->Code == 'OK') {
            return [0, ''];
        } else {
            return [2, $result->Message];
        }
    }

    /**
     * 生成验证码
     * @return \think\db\Query
     */
    public static function makeCode() {
        return rand(100000,999999);
    }

    /**
     * 数据库:添加数据
     * @param string $phone 手机号
     * @param string $signName 模板签名
     * @param string $templateCode 模板ID
     * @param string $code 验证码
     * @return array[Db, ID]
     */
    public static function addData($ip, $phone, $signName, $templateCode, $code = '') {
        $db = Db::name('all_users_sms');
        $id = $db->insertGetId([
            'phone' => $phone,
            'signName' => $signName,
            'template' => $templateCode,
            'code' => $code,
            'create_ip' => $ip,
            'create_day' => date('Ymd'),
            'create_time' => time()
        ]);
        return [$db, $id];
    }

    /**
     * 发送
     * @param Db $db 数据库对象
     * @param string $id 流水号ID
     * @param string $phone 手机号
     * @param string $signName 模板签名
     * @param string $templateCode 模板ID
     * @param array $data 模板数据
     * @return acsResponse
     */
    public static function sendSms($db, $id, $phone, $signName, $templateCode, $data) {
        $config = self::$config;
        //短信API产品名
        $product = "Dysmsapi";
        //短信API产品域名
        $domain = "dysmsapi.aliyuncs.com";
        //暂时不支持多Region
        $region = "cn-hangzhou";

        //初始化访问的acsCleint
        $profile = DefaultProfile::getProfile($region, $config['app_key'], $config['app_secret']);
        DefaultProfile::addEndpoint("cn-hangzhou", "cn-hangzhou", $product, $domain);
        $acsClient= new DefaultAcsClient($profile);

        $request = new SendSmsRequest();
        //必填-短信接收号码
        $request->setPhoneNumbers($phone);
        //必填-短信签名
        $request->setSignName($signName);
        //必填-短信模板Code
        $request->setTemplateCode($templateCode);
        //选填-假如模板中存在变量需要替换则为必填(JSON格式)
        $request->setTemplateParam(json_encode($data));
        //选填-发送短信流水号
        $request->setOutId($id);

        //发起访问请求
        if ($config['sandbox']) {
            // 沙盒
            $acsResponse = (object)[
                'Code' => 'OK',
                'RequestId' => '1EA7DA3B-C743-4335-BDAE-2E24013F9BFE',
                'BizId' => '108378691287^1111329563383',
                'Message' => 'OK',
            ];
        } else {
            $acsResponse = $acsClient->getAcsResponse($request);
        }
        $acsResponse->Code != 'OK' && $acsResponse->BizId = '';
        // 写入数据库
        $db->where('id', $id)->update([
            'cloudRequestId' => $acsResponse->RequestId,
            'cloudBizId' => $acsResponse->BizId,
            'cloudCode' => $acsResponse->Code,
            'cloudMessage' => $acsResponse->Message
        ]);

        return $acsResponse;
    }

}
