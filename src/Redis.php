<?php
// +----------------------------------------------------------------------
// | Author: phoenix <luoxuan5678@qq.com>
// +----------------------------------------------------------------------
namespace Yufeixuan;

use Predis\Client;

/**
 * Redis接口类
 * @doc http://doc.redisfans.com/
 * */
class Redis
{
    protected static $config = [
        'host'       => '127.0.0.1',
        'port'       => 6379,
        // 'password'   => '',
        // 'select'     => 0,
        // 'timeout'    => 3600,
        // 'expire'     => 0,
        // 'persistent' => false,
        // 'prefix'     => '',
    ];

    protected static $redis;
    private static $_instance;
    /**
     * 构造函数
     * @param array $config 缓存参数
     * @access public
     */
    public function __construct($config = [])
    {
        if (!empty($config)) {
            // 合并重复项
            static::$config = array_merge(static::$config, $config);
        }

        static::$redis = new Client(static::$config);
        $a = new Client(static::$config);
//         $a->lpush($key, $values)
//         $a->llen($key)
    }
    
    
    //获取redis实例
    public static function getInstence($config = [])
    {
        if (!(self::$_instance instanceof self)) {
            if (!empty($config)) {
                // 合并重复项
                static::$config = array_merge(static::$config, $config);
            }
            
            static::$redis = new Client(static::$config);
            self::$_instance = new self();
        }
        return self::$_instance;  
    }

    
/**
     * 设置缓存
     * @param string $key 缓存标识
     * @param mixed  $value 缓存的数据
     * @param int    $time 缓存过期时间
     * @param string $unit 指定时间单位 （h/m/s/ms）
     * @throws \Exception
     * */
    public function set($key, $value, $time = null, $unit = null)
    {
        if (empty($key) || empty($value)) {
            return false;
        }

        // 如果传入的是数组，那么就编码下
        $value = is_array($value) ? json_encode($value) : $value;

        if ($time) {
            if ($unit) {
                // 设置了过期时间并使用了快捷时间单位
                // 判断时间单位
                switch (strtolower($unit)) {
                    case 'h':
                        $time *= 3600;
                        break;
                    case 'm':
                        $time *= 60;
                        break;
                    case 's':
                        break;
                    case 'ms':
                        break;
                    default:
                        return false;
                        break;
                }

                if (strtolower($unit) === 'ms') {
                    // 毫秒秒为单位的到期值
                    return static::_psetex($key, $value, (int) $time);
                }
            }

            // 秒为单位的到期值
            return static::_setex($key, $value, (int) $time);
        } 
        else {
            // 不设置过期时间
            return static::$redis->set($key, $value);
        }
    }
    
    /**
     * 将 key 的值设为 value,当且仅当 key不存在。
     * @param string $key 键
     * @param string $value 值
     */
    public static function setnx($key, $value)
    {
        if (empty($key) || empty($value)) {
            return false;
        }
        
        return static::$redis->setnx($key,$value);
    }

    /**
     * 获取缓存
     * @param string $key 缓存标识
     * @return mixed
     * @throws \Exception
     * */
    public static function get($key)
    {
        if (empty($key)) {
            return false;
        }

        return static::$redis->get($key);
    }
    
    /**
     * 获取指定字段hash值
     * @param string $key 键名
     * @param string $field 字段名
     */
    public static function hget($key, $field)
    {
        if (empty($key) || empty($field)) {
            return false;
        }
        
        return static::$redis->hget($key, $field);
    }
    
    /**
     * 获取指定键名所有值
     * @param string $key
     */
    public static function hgetall($key)
    {
        if (empty($key)) {
            return false;
        }
        
        return static::$redis->hgetall($key);
    }
    
    /**
     * 设置hash值
     * @param string $key
     * @param string $field
     * @param string $value
     * @return boolean
     */
    public static function hset($key, $field, $value)
    {
        if (empty($key) || empty($field) || empty($value)) {
            return false;
        }
        
        return static::$redis->hset($key, $field, $value);
    }
    
    /**
     * 将一个或多个值 value 插入到列表 key 的表头
     * @param string $key
     * @param string $value
     * @return boolean
     */
    public static function lpush($key, $value)
    {
        if (empty($key) || empty($value)) {
            return false;
        }
        
        return static::$redis->lpush($key, $value);
    }
    
    /**
     * 移除并返回列表 key的头元素。
     * @param string $key
     * @return boolean 当 key不存在时返回 nil
     */
    public static function lpop($key)
    {
        if (empty($key)) {
            return false;
        }
        
        return static::$redis->lpop($key);
    }
    
    /**
     * 将一个或多个值 value 插入到列表 key 的表尾(最右边) 如果 key 不存在，一个空列表会被创建并执行 RPUSH 操作
     * @param string $key
     * @param string $value
     * @return boolean
     */
    public static function rpush($key, $value)
    {
        if (empty($key) || empty($value)) {
            return false;
        }
        
        return static::$redis->rpush($key, $value);
    }
    
    /**
     * 移除并返回列表 key 的尾元素。
     * @param unknown $key
     * @return boolean 当 key 不存在时，返回 nil 
     */
    public static function rpop($key)
    {
        if (empty($key)) {
            return false;
        }
        
        return static::$redis->rpop($key);
    }
    
    /**
     * 返回list列表 key 的长度
     * @param string $key
     */
    public static function llen($key)
    {
        if (empty($key)) {
            return false;
        }
        
        return static::$redis->llen($key);
    }
    
    /**
     * 返回hash列表 key 的长度
     * @param string $key
     */
    public static function hlen($key)
    {
        if (empty($key)) {
            return false;
        }
        
        return static::$redis->hlen($key);
    }
    
    
    
    /**
     * 设置hash值,当且仅当域 field不存在时
     * @param string $key
     * @param string $field
     * @param string $value
     * @return boolean 设置成功返回 1,否则返回0,错误返回false
     */
    public static function hsetnx($key, $field, $value)
    {
        if (empty($key) || empty($field)) {
            return false;
        }
        
        return static::$redis->hsetnx($key, $field);
    }
    
    

    /**
     * 删除指定缓存
     * @param string $key 缓存标识
     * @return int 返回删除个数
     * */
    public static function del($key)
    {
        if (empty($key)) {
            return false;
        }

        return static::$redis->del($key);
    }

    /**
     * 判断缓存是否在 Redis 内
     * @param string $key 缓存标识
     * @return int 返回存在个数
     * */
    public static function exists($key)
    {
        if (empty($key)) {
            return false;
        }

        return static::$redis->exists($key);
    }


    /**
     * 设置以秒为过期时间单位的缓存
     * @param string $key 缓存标识
     * @param mixed  $value 缓存的数据
     * @param int    $time 缓存过期时间
     * @throws \Exception
     * */
    private static function _setex($key, $value, $time)
    {
        // https://github.com/nrk/predis/issues/203
        return static::$redis->setex($key, $time, $value);
    }

    /**
     * 设置以毫秒为过期时间单位的缓存
     * @param string $key 缓存标识
     * @param mixed  $value 缓存的数据
     * @param int    $time 缓存过期时间
     * @throws \Exception
     * */
    private static function _psetex($key, $value, $time)
    {
        return static::$redis->psetex($key, $time, $value);
    }
}