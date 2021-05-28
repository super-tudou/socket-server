<?php
/**
 * Created by PhpStorm.
 * @file   LogTrait.php
 * @author 李锦 <jin.li@vhall.com>
 * @date   2021/4/1 4:04 下午
 * @desc   LogTrait.php
 */

namespace component;

/**
 * 日志组件
 * Trait LogTrait
 * @package component
 */
trait LogTrait
{
    /**
     * 进度ID
     * @var string
     */
    protected $processId = '0';

    /**
     * @param string $processId
     */
    public function setProcessId(string $processId): void
    {
        $this->processId = $processId;
    }

    /**
     * @return false|string
     */
    private function getTime()
    {
        return date("Y-m-d H:i:s");
    }

    /**
     * @param $info
     * @param array $context
     */
    public function info($info, $context = [])
    {
        $time = $this->getTime();
        empty($this->processId) && $this->processId = getmypid();
        echo "[info][{$this->processId}][{$time}]" . $info, PHP_EOL;
    }

    /**
     * @param $info
     * @param array $context
     */
    public function error($info, $context = [])
    {
        $time = $this->getTime();
        empty($this->processId) && $this->processId = getmypid();
        echo "[error][{$this->processId}][{$time}]" . $info, PHP_EOL;
        exit;
    }

    /**
     * @param $info
     * @param array $context
     */
    public function waring($info, $context = [])
    {
        $time = $this->getTime();
        empty($this->processId) && $this->processId = getmypid();
        echo "[waring][{$this->processId}][{$time}]" . $info, PHP_EOL;
    }
}
