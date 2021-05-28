<?php
/**
 * Created by PhpStorm.
 * @file   BaseClass.php
 * @author 李锦 <jin.li@vhall.com>
 * @date   2021/4/9 3:41 下午
 * @desc   BaseClass.php
 */

namespace common;

use component\LogTrait;

/**
 * 基础类
 * Class BaseClass
 * @package common
 */
class BaseClass
{
    use LogTrait;

    /**
     * @var array
     */
    public static $_instance = [];

    /**
     * @param $params
     */
    protected function __init($params)
    {

    }

    /**
     * aaa constructor.
     * @param array $params
     */
    public function __construct($params = [])
    {
        $this->__init($params);
    }

    /**
     * @param array $params
     * @return static
     */
    public static function getInstance($params = [])
    {
        $className = static::class;
        if (!isset(self::$_instance[$className]) || !self::$_instance[$className] instanceof static) {
            self::$_instance[$className] = new static($params);
        }
        return self::$_instance[$className];
    }
}
