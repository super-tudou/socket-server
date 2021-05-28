<?php
/**
 * Created by PhpStorm.
 * @file   ConfigServer.php
 * @author 李锦 <jin.li@vhall.com>
 * @date   2021/4/28 下午3:53
 * @desc   ConfigServer.php
 */

namespace service;

use Symfony\Component\Yaml\Yaml;


/**
 * 配置服务
 * Class ConfigService
 * @package service
 */
class ConfigService extends BaseService
{
    private $config = [];

    protected function __init($params)
    {

        $configFile = $params['config_file'] ?? __DIR__.'/../config/config.yml';
        if (!is_file($configFile)) {
            $this->error("config file is empty![{$configFile}]");
        }
        $this->config = Yaml::parseFile($configFile);
    }

    public function getConfig($key)
    {
        $keys = explode(".", $key);
        $config = $this->config;
        foreach ($keys as $key) {
            $config = $this->getConfigValue($config, $key);
        }
        return $config;
    }

    private function getConfigValue($config, $key)
    {
        if (isset($config[$key])) {
            return $config[$key];
        } else {
            $this->error("config not exist, key[{$key}]");
        }
    }

    public static function get($key)
    {
        $configService = ConfigService::getInstance();
        return $configService->getConfig($key);
    }
}

