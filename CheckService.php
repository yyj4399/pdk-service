<?php

// +----------------------------------------------------------------------
// | Kunpeng游戏后台管理系统
// +----------------------------------------------------------------------
// | 版权所有 2017~2017 上海鲲佑科技有限公司 [  ]
// +----------------------------------------------------------------------
// | 官方网站:
// +----------------------------------------------------------------------

namespace service;

/**
 * 验证服务
 * Class CheckService
 * @package service
 * @author 杨亚军(Yang Yajun) <yyj4399@gmail.com>
 */
class CheckService {

    /**
     * 验证-必须的参数是否存在
     * @param array $list 参数列表
     * @param array $arr 参数数组
     * @param array $conf 配置
     * @return array [状态, 参数1, 参数2...]
     */
    public static function allow(array $list, array $arr, array $conf) {
        // 验证是否为空
        $res = [0];
        foreach ($list as $k => $v) {
            switch ($v) {
                case 'phone': // 手机号
                    $status = self::phone($arr, $v);
                    break;
                case 'type': // 短信验证码类型
                    $status = self::type($arr, $v, $conf);
                    break;
                case 'password': // 密码
                    $status = self::password($arr, $v);
                    break;
                case 'code': // 验证码
                    $status = self::code($arr, $v);
                    break;
                case 'userId': // 用户Id
                    $status = self::userId($arr, $v);
                    break;
                case 'mcKey': // mcKey
                    $status = self::mcKey($arr, $v);
                    break;
                case 'handleId': // 登录标记
                    $status = self::handleId($arr, $v);
                    break;
                case 'fee': // 金额
                    $status = self::fee($arr, $v);
                    break;
                case 'body': // 商品描述
                    $status = self::body($arr, $v);
                    break;
                case 'orderId': // 商品描述
                    $status = self::orderId($arr, $v);
                    break;
                case 'userIdx': // 商品描述
                    $status = self::userId($arr, $v);
                    break;
                case 'img_type': // 商品描述
                    $status = self::img_type($arr, $v);
                    break;
                default:
                    # code...
                    break;
            }
            // 返回错误
            if ($status != 0) {
                $res[0] = $status;
                // 保持参数对齐
                for ($i = 0; $i < count($list); $i++) {
                    $res[$i+1] = '';
                }
                return $res;
            }
            if (!isset($arr[$v])) {
                $res[$k+1] = null;
            }
            $res[$k+1] = $arr[$v];
        }
        return $res;
    }

    /**
     * 验证-手机号码
     * @param array $arr 参数数组
     * @param string $v 参数名
     * @param array $conf 配置
     * @param int 状态码
     */
    public static function phone(array $arr, string $v) {
        // 手机号码是否存在
        if (!isset($arr[$v])) {
            return 401;
        }
        // 手机号码是否为空
        if (empty($arr[$v])) {
            return 401;
        }
        // 正则匹配手机号码
        if (preg_match_all('/^1[0-9]{10}$/', $arr[$v]) != 1) {
            return 401;
        }
        return 0;
    }

    /**
     * 验证-短信验证码类型
     * @param array $arr 参数数组
     * @param string $v 参数名
     * @param int 状态码
     */
    public static function type(array $arr, string $v,array $conf) {
        // 短信验证码类型是否存在
        if (!isset($arr[$v])) {
            return 401;
        }
        // 短信验证码类型是否为空
        if (empty($arr[$v])) {
            return 401;
        }
        // 短信验证码类型枚举
        if (!isset($conf['sms']['TemplateCode'][$arr[$v]])) {
            return 401;
        }
        return 0;
    }

    /**
     * 验证-密码
     * @param array $arr 参数数组
     * @param string $v 参数名
     * @param int 状态码
     */
    public static function password(array $arr, string $v) {
        // 密码是否存在
        if (!isset($arr[$v])) {
            return 401;
        }
        // 密码是否为空
        if (empty($arr[$v])) {
            return 401;
        }
        // 检查密码长度
        if (strlen($arr[$v]) < 6 || strlen($arr[$v]) > 32) {
            return 401;
        }
        return 0;
    }

    /**
     * 验证-验证码
     * @param array $arr 参数数组
     * @param string $v 参数名
     * @param int 状态码
     */
    public static function code(array $arr, string $v) {
        // 验证码是否存在
        if (!isset($arr[$v])) {
            return 401;
        }
        // 验证码是否为空
        if (empty($arr[$v])) {
            return 401;
        }
        return 0;
    }

    /**
     * 验证-用户Id
     * @param array $arr 参数数组
     * @param string $v 参数名
     * @param int 状态码
     */
    public static function userId(array $arr, string $v) {
        // 用户Id是否存在
        if (!isset($arr[$v])) {
            return 401;
        }
        // 用户Id是否为空
        if (empty($arr[$v])) {
            return 401;
        }
        // 用户Id是否是整数
        if (!is_numeric($arr[$v])) {
            return 401;
        }
        return 0;
    }

    /**
     * 验证-McKey
     * @param array $arr 参数数组
     * @param string $v 参数名
     * @param int 状态码
     */
    public static function mcKey(array $arr, string $v) {
        // McKey是否存在
        if (!isset($arr[$v])) {
            return 401;
        }
        // McKey是否为空
        if (empty($arr[$v])) {
            return 401;
        }
        return 0;
    }

    /**
     * 验证-handleId
     * @param array $arr 参数数组
     * @param string $v 参数名
     * @param int 状态码
     */
    public static function handleId(array $arr, string $v) {
        // handleId是否存在
        if (!isset($arr[$v])) {
            return 401;
        }
        // handleId是否为空
        if (empty($arr[$v])) {
            return 401;
        }
        // handleId是否是整数
        if (!is_numeric($arr[$v])) {
            return 401;
        }
        return 0;
    }

    /**
     * 验证-金额
     * @param array $arr 参数数组
     * @param string $v 参数名
     * @param int 状态码
     */
    public static function fee(array $arr, string $v) {
        // 金额是否存在
        if (!isset($arr[$v])) {
            return 401;
        }
        // 金额是否为空
        if (empty($arr[$v])) {
            return 401;
        }
        // 金额是否是整数
        if (!is_numeric($arr[$v])) {
            return 401;
        }
        // 金额是否大于0
        if ($arr[$v] < 0 || $arr[$v] == 0) {
            return 401;
        }
        return 0;
    }

    /**
     * 验证-商品描述
     * @param array $arr 参数数组
     * @param string $v 参数名
     * @param int 状态码
     */
    public static function body(array $arr, string $v) {
        // 金额是否存在
        if (!isset($arr[$v])) {
            return 401;
        }
        // 金额是否为空
        if (empty($arr[$v])) {
            return 401;
        }
        return 0;
    }

    /**
     * 验证-订单ID
     * @param array $arr 参数数组
     * @param string $v 参数名
     * @param int 状态码
     */
    public static function orderId(array $arr, string $v) {
        // 订单ID是否存在
        if (!isset($arr[$v])) {
            return 401;
        }
        // 订单ID是否为空
        if (empty($arr[$v])) {
            return 401;
        }
        return 0;
    }

    /**
     * 验证-图片类型
     * @param array $arr 参数数组
     * @param string $v 参数名
     * @param int 状态码
     */
    public static function img_type(array $arr, string $v) {
        // 图片类型是否存在
        if (!isset($arr[$v])) {
            return 401;
        }
        // 图片类型是否为空
        if (empty($arr[$v])) {
            return 401;
        }
        return 0;
    }

}
