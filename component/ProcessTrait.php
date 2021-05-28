<?php
/**
 * Created by PhpStorm.
 * @file   Processlist.php
 * @author 李锦 <jin.li@vhall.com>
 * @date   2021/4/9 2:27 下午
 * @desc   Processlist.php
 */

namespace component;

/**
 * 多进程组件
 * Trait ProcessTrait
 * @package component
 */
trait ProcessTrait
{

    /**
     * @var array
     */
    public $processList = [];
    /**
     * @var int
     */
    public $processCount = 1;

    /**
     * @var \Closure
     */
    public $executeCallback = null;
    /**
     * @var \Closure
     */
    public $waitCallback = null;

    /**
     * fork 前调用
     * @var
     */
    public $beforeFork = null;

    /**
     * @param \Closure $callback
     */
    public function setCallback(\Closure $callback): void
    {
        $this->executeCallback = $callback;
    }

    /**
     * @param int $processCount
     */
    public function setProcessCount(int $processCount): void
    {
        $this->processCount = $processCount;
    }

    /**
     * @param \Closure $waitCallback
     */
    public function setWaitCallback(?\Closure $waitCallback): void
    {
        $this->waitCallback = $waitCallback;
    }

    /**
     * 启动进程
     */
    public function run()
    {
        $this->forkProcess();
        $this->waitProcess();
    }


    /**
     * fork process
     */
    private function forkProcess()
    {
        if ($this->processCount < 1) {
            $this->error("process count error.", ['process_count' => $this->processCount]);
        }
        for ($i = 0; $i < $this->processCount; $i++) {
            $this->beforeFork && \call_user_func($this->beforeFork, $this);
            $pid = pcntl_fork();
            if ($pid == -1) {
                die("创建子进程失败");
            } elseif ($pid > 0) {
                $this->processList[$pid] = $pid;
            } else {
                $this->executeAction($i);
                exit();
            }
        }
    }


    /**
     * child action execute
     * @param $sign
     */
    protected function executeAction($sign)
    {
        if ($this->executeCallback) {
            call_user_func($this->executeCallback, $this);
        } else {
            $this->error("child execute function is null");
        }
    }

    /**
     * 主进程等待
     */
    protected function waitProcess()
    {
        if ($this->waitCallback) {
            call_user_func($this->waitCallback, $this);
        } else {
            $this->info("start main process listen......");
            while (count($this->processList)) {
                $childPid = pcntl_wait($status);
                if ($childPid > 0) {
                    unset($this->processList[$childPid]);
                }
            }
        }
    }

}
