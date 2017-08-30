<?php

// +----------------------------------------------------------------------
// | 服务驱动
// +----------------------------------------------------------------------
// | 版权所有 2017~2017 上海鲲佑信息科技有限公司 [ www.51kunyou.com ]
// +----------------------------------------------------------------------
// | 官方网站: www.51kunyou.com
// +----------------------------------------------------------------------

namespace service;

use think\Session;
use think\Db;
use think\Cache;
use think\Request;
use think\Validate;

use service\CheckService;
use service\DataService;

/**
 * 访问权限服务
 * Class AuthService
 * @package service
 * @author 杨亚军(Yang Yajun) <yyj4399@gmail.com>
 */
class AuthService {

    /**
     * 获取用户对象
     * @return array $user
     */
    public static function getUser() {
        // 登录方式-Session
        $user = Session::get('user');
        // 初始化用户对象
        if (empty($user) || (IS_DEV && $user['id'] == 0)) {
            $user = [
                'id' => 0,
                'phone' => '',
                'lv' => 0,
                'point' => 0
            ];
            Session::set('user', $user);
        }
        // 返回
        return $user;
    }

    /**
     * 执行-登录
     * @param array $user 用户
     * @return null
     */
    public static function goSignIn($user) {
        // 登录方式-Session
        Session::set('user', $user);
    }

    /**
     * 执行-登出
     * @return null
     */
    public static function goSignOut() {
        // 登录方式-Session
        Session::delete('user');
    }

    /**
     * 更新用户资料
     * @return null
     */
    public static function update() {
        // 登录方式-Session
        $userId = Session::get('user')['id'];
        if (!empty($userId)) {
            Session::set('user', Db::name('all_users')->where(['id' => $userId])->find());
        }
    }

    /**
     * 获取尝试次数
     * @return int $tryTimes
     */
    public static function getTryTimes() {
        // 记录方式-Cache
        return Cache::remember('tryTimes@' . Request::instance()->ip(), function() {
          	return 0;
        });
    }

    /**
     * 增加并获取尝试次数
     * @return int $tryTimes
     */
    public static function postTryTimes() {
        // 记录方式-Cache
        return Cache::inc('tryTimes@' . Request::instance()->ip());
    }

    /**
     * 清空尝试次数
     * @return null
     */
    public static function deleteTryTimes() {
        // 记录方式-Cache
        return Cache::rm('tryTimes@' . Request::instance()->ip());
    }

    /**
     * 参数处理
     * @param array $list 需要的参数
     * @param array $params 参数
     * @param array $config 配置
     * @return null
     */
    public static function paramsInit($list, $params, $config = []) {
        $params = DataService::rqs($params);
        return CheckService::allow($list, $params, $config);
    }

    /**
     * 登录(id)
     * @param array $params 参数
     * @return null
     */
    public static function signInById($params) {
        // 解析参数
        list($status, $userId, $mcKey, $nickName) = self::paramsInit(['userId', 'mcKey', 'nickName'], $params);
        if ($status != 0) {
            return $status;
        }
        // 检查用户是否存在
        $user = Db::name('all_users')->where('id', $userId)->find();
        if (empty($user)) {
            return 124;
        }
        // 检查mcKey
        if ($mcKey != $user['mcKey']) {
            return 127;
        }
        // 登录
        self::goSignIn($user);
        if (!empty($nickName)) {
            Db::name('all_users')->where(['id' => $user['id']])->update(['name' => $nickName]);
        }
        return 0;
    }

    /**
     * 登录(手机号)
     * @param array $params 参数
     * @return null
     */
    public static function signInByPhone($params) {
        // 解析参数
        list($status, $phone, $password) = self::paramsInit(['phone', 'password'], $params);
        if ($status != 0) {
            return $status;
        }
        // 检查用户是否存在
        $user = Db::name('all_users')->where('phone', $phone)->find();
        if (empty($user)) {
            return 124;
        }
        // 验证密码
        if ($user['password'] != DataService::password($password)) {
            return 128;
        }
        // 登录
        self::goSignIn($user);
        return 0;
    }

}
