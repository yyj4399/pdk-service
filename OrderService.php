<?php

// +----------------------------------------------------------------------
// | Kunpeng游戏奖品中心
// +----------------------------------------------------------------------
// | 版权所有 2017~2017 上海鲲佑科技有限公司 [ www.51kunyou.com ]
// +----------------------------------------------------------------------
// | 官方网站: www.51kunyou.com
// +----------------------------------------------------------------------

namespace service;

use think\Db;
use service\ApiService;
use service\AuthService;

/**
 * 订单服务
 * Class OrderService
 * @package service
 * @author 杨亚军(Yang Yajun) <yyj4399@gmail.com>
 */
class OrderService {

    /**
     * 获取用户积分
     * @param int $userId 用户ID
     * @return int
     */
    public static function getUserPoint($userId) {
        // 获取积分
        $result = ApiService::getUserPoint($userId);
        if (!isset($result['Cmd']) || $result['Cmd'] != 'GetPoint') {
            return 0;
        }
        // 更新用户信息
        $data = [
            'point' => $result['Points']
        ];
        if (isset($result['NickName'])) {
            $data['name'] = $result['NickName'];
        }
        Db::name('all_users')->where(['id' => $userId])->update($data);
        AuthService::update();
        // 返回用户积分
        return $result['Points'];
    }

    /**
     * 创建订单
     * @param array $user 用户
     * @param array $pro 商品
     * @param string $address 地址
     * @return null
     */
    public static function create($user, $pro, $address) {
        $result = ApiService::postUserPoint($user['id'], -$pro['point']);
        if (!isset($result['Cmd']) || $result['Cmd'] != 'ExchangePoint') {
            return null;
        }
        $data = [
            'userId' => $user['id'],
            'userPhone' => $user['phone'],
            'point' => $pro['point'],
            'pro_id' => $pro['id'],
            'name' => $pro['text'],
            'address' => (string) $address,
            'create_time' => time()
        ];
        $order_id = Db::name('point_orders')->insertGetId($data);
        Db::name('all_users')->where(['id' => $user['id']])->update(['point' => $user['point']-$pro['point']]);
        return $order_id;
    }

}
