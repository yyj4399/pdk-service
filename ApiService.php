<?php

// +----------------------------------------------------------------------
// | 服务驱动
// +----------------------------------------------------------------------
// | 版权所有 2017~2017 上海鲲佑信息科技有限公司 [ www.51kunyou.com ]
// +----------------------------------------------------------------------
// | 官方网站: www.51kunyou.com
// +----------------------------------------------------------------------

namespace service;

use think\Db;
use service\HttpService;

/**
 * 访问权限服务
 * Class AuthService
 * @package service
 * @author 杨亚军(Yang Yajun) <yyj4399@gmail.com>
 */
class ApiService {

    /**
     * 远程Api路径
     */
    public static $url = 'http://139.196.144.70:10086/';

    /**
     * 接口-获取用户积分
     * @param int $userId 用户ID
     * @return json
     */
    public static function getUserPoint($userId) {
        return json_decode(HttpService::postGet(self::$url . 'GetPoint', [
            'userId' => $userId
        ]), true);
    }

    /**
     * 接口-修改用户积分
     * @param int $userId 用户ID
     * @return json
     */
    public static function postUserPoint($userId, $point) {
        return json_decode(HttpService::postGet(self::$url . 'ExchangePoint', [
            'userId' => $userId,
            'exchangePoints' => $point
        ]), true);
    }

}
