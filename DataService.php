<?php

// +----------------------------------------------------------------------
// | Kunpeng游戏后台管理系统
// +----------------------------------------------------------------------
// | 版权所有 2017~2017 上海鲲佑科技有限公司 [ (null) ]
// +----------------------------------------------------------------------
// | 官方网站: (null)
// +----------------------------------------------------------------------

namespace service;

use think\Db;

/**
 * 数据服务
 * Class DataService
 * @package service
 * @author 杨亚军(Yang Yajun) <yyj4399@gmail.com>
 */
class DataService {

    private static $config = [
        'des' => [
            // 加密密钥 key (最大为8个字节)
            'key' => 'jkHuIy9D',
            // 初始向量 iv (最大为8个字节)
            'iv' => 'Mi9l/+7Z'
        ]
    ];

    /**
     * 解析参数
     * @param string $password 密码原文
     * @return string 加密后的密码
     */
    public static function rqs($params) {
        isset($params['userId']) && $params['userId'] = self::desDecode($params['userId']);
        return $params;
    }

    /**
     * 用户密码加密
     * @param string $password 密码原文
     * @return string 加密后的密码
     */
    public static function password($password) {
        $string = '';
        $j = strlen($password);
        if ($j < 4 || $j > 32) {
            return false;
        }
        for($i = 0; $i < $j; $i++){
            $string .= md5(md5($password[$i] . $password[($i % 2)]));
        }
        return md5('@' . $string);
    }

    /**
     * DES加密
     * @param string $data 数据
     * @param string $key 密钥
     * @param string $iv 初始向量
     * @return string 加密结果
     */
    public static function desEncode(string $data, $key = 'jkHuIy9D', $iv = 'Mi9l/+7Z') {
        // 打开 Mcrypt 加密模块, 使用DES算法, cbc模式
        $td = mcrypt_module_open(MCRYPT_DES, '', 'cbc', '');
        // 初始化, 设置密钥和初始向量
        mcrypt_generic_init($td, $key, $iv);
        // 加密
        $encode = mcrypt_generic($td, $data);
        // 清理 Mcrypt 加密模块
        mcrypt_generic_deinit($td);
        // 关闭 Mcrypt 加密模块
        mcrypt_module_close($td);
        return base64_encode($encode);
    }

    /**
     * DES解密
     * @param string $data 数据
     * @param string $key 密钥
     * @param string $iv 初始向量
     * @return string 解密结果
     */
    public static function desDecode(string $data, $key = 'jkHuIy9D', $iv = 'Mi9l/+7Z') {
        $data = base64_decode($data);
        // 打开 Mcrypt 加密模块, 使用DES算法, cbc模式
        $td = mcrypt_module_open(MCRYPT_DES, '', 'cbc', '');
        // 初始化, 设置密钥和初始向量
        mcrypt_generic_init($td, $key, $iv);
        // 解密(去掉不足8字节的补足0)
        $decode = rtrim(mdecrypt_generic($td, $data), "\0");
        // 清理 Mcrypt 加密模块
        mcrypt_generic_deinit($td);
        // 关闭 Mcrypt 加密模块
        mcrypt_module_close($td);
        return $decode;
    }

    /**
     * byte转换
     * @param string $string 待转换字符串
     * @return array
     */
    public static function byte(string $string) {
        for ($i = 0;$i < strlen($string); $i++) {
            $arr[] = ord($string[$i]);
        }
        return $arr;
    }

    /**
     * byte转换
     * @param array $bytes 待转换ASCII数组
     * @return string
     */
    public static function string(array $bytes) {
        $str = '';
        foreach ($bytes as $v) {
            $str .= chr($v);
        }
        return $str;
    }

    /**
     * 数据查询(select)
     * @param \think\db\Query|string $db 数据查询对象
     * @param array $data 需要保存或更新的数据
     * @param string $upkey 条件主键限制
     * @param array $where 其它的where条件
     * @return bool
     */
    public static function select(&$db, $where = []) {
        if (is_string($db)) {
            $db = Db::name($db);
        }
        $fields = $db->getTableFields(['table' => $db->getTable()]);
        $_data = [];
        foreach ($data as $k => $v) {
            in_array($k, $fields) && ($_data[$k] = $v);
        }
        if (self::_apply_save_where($db, $data, $upkey, $where)->count() > 0) {
            return self::_apply_save_where($db, $data, $upkey, $where)->update($_data) !== FALSE;
        }
        return self::_apply_save_where($db, $data, $upkey, $where)->insert($_data) !== FALSE;
    }

    /**
     * 删除指定序号
     * @param string $sequence
     * @param string $type
     * @return bool
     */
    public static function deleteSequence($sequence, $type = 'SYSTEM') {
        $data = ['sequence' => $sequence, 'type' => strtoupper($type)];
        return Db::name('SystemSequence')->where($data)->delete();
    }

    /**
     * 生成唯一序号 (失败返回 NULL )
     * @param int $length 序号长度
     * @param string $type 序号顾类型
     * @return string
     */
    public static function createSequence($length = 10, $type = 'SYSTEM') {
        $times = 0;
        while ($times++ < 10) {
            $sequence = '';
            $i = 0;
            while ($i++ < $length) {
                $sequence .= ($i <= 1 ? rand(1, 9) : rand(0, 9));
            }
            $data = ['sequence' => $sequence, 'type' => strtoupper($type)];
            if (Db::name('SystemSequence')->where($data)->count() < 1 && Db::name('SystemSequence')->insert($data)) {
                return $sequence;
            }
        }
        return null;
    }

    /**
     * 数据增量保存
     * @param \think\db\Query|string $db 数据查询对象
     * @param array $data 需要保存或更新的数据
     * @param string $upkey 条件主键限制
     * @param array $where 其它的where条件
     * @return bool
     */
    public static function save($db, $data, $upkey = 'id', $where = []) {
        if (is_string($db)) {
            $db = Db::name($db);
        }
        $fields = $db->getTableFields(['table' => $db->getTable()]);
        $_data = [];
        foreach ($data as $k => $v) {
            in_array($k, $fields) && ($_data[$k] = $v);
        }
        if (self::_apply_save_where($db, $data, $upkey, $where)->count() > 0) {
            return self::_apply_save_where($db, $data, $upkey, $where)->update($_data) !== FALSE;
        }
        return self::_apply_save_where($db, $data, $upkey, $where)->insert($_data) !== FALSE;
    }

    /**
     * 应用 where 条件
     * @param \think\db\Query|string $db 数据查询对象
     * @param array $data 需要保存或更新的数据
     * @param string $upkey 条件主键限制
     * @param array $where 其它的where条件
     * @return \think\db\Query
     */
    protected static function _apply_save_where(&$db, $data, $upkey, $where) {
        foreach (is_string($upkey) ? explode(',', $upkey) : $upkey as $v) {
            if (is_string($v) && array_key_exists($v, $data)) {
                $db->where($v, $data[$v]);
            } elseif (is_string($v)) {
                $db->where("{$v} IS NULL");
            }
        }
        return $db->where($where);
    }

    /**
     * 更新数据表内容
     * @param \think\db\Query|string $db 数据查询对象
     * @param array $where 额外查询条件
     * @return bool|null
     */
    public static function update(&$db, $where = []) {
        if (is_string($db)) {
            $db = Db::name($db);
        }
        $ids = explode(',', input("post.id", ''));
        $field = input('post.field', '');
        $value = input('post.value', '');
        $pk = $db->getPk(['table' => $db->getTable()]);
        $db->where(empty($pk) ? 'id' : $pk, 'in', $ids);
        !empty($where) && $db->where($where);
        // 删除模式
        if ($field === 'delete') {
            $fields = $db->getTableFields(['table' => $db->getTable()]);
            if (in_array('is_deleted', $fields)) {
                return false !== $db->update(['is_deleted' => 1]);
            }
            return false !== $db->delete();
        }
        // 更新模式
        return false !== $db->update([$field => $value]);
    }

}
