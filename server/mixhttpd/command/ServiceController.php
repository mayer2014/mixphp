<?php

/**
 * 控制器
 * @author 刘健 <coder.liu@qq.com>
 */

namespace mixhttpd\command;

use mix\console\Controller;

class ServiceController extends Controller
{

    // 服务是否启动
    public function isStart()
    {
        $output = \Mix::app()->exec('ps -ef | grep mixhttpd');
        foreach ($output as $item) {
            if (strpos($item, 'mixhttpd master') !== false) {
                return true;
            }
        }
        return false;
    }

    // 启动服务
    public function actionStart()
    {
        if ($this->isStart()) {
            return 'mixhttpd is running' . PHP_EOL;
        }
        $server = \Mix::app()->server;
        if (!is_null(\Mix::app()->request->param('hot-update'))) {
            $server->setting['max_request'] = 1;
        }
        if (!is_null(\Mix::app()->request->param('foreground'))) {
            $server->setting['daemonize'] = false;
        }
        return $server->start();
    }

    // 停止服务
    public function actionStop()
    {
        if ($this->isStart()) {
            \Mix::app()->exec('ps -ef | grep mixhttpd | awk \'NR==1{print $2}\' | xargs -n1 kill');
        }
        while ($this->isStart()) {
        }
        return 'mixhttpd stop complete' . PHP_EOL;
    }

    // 重启服务
    public function actionRestart()
    {
        $this->actionStop();
        $this->actionStart();
    }

    // 查看服务状态
    public function actionStatus()
    {
        if (!$this->isStart()) {
            return 'mixhttpd is not running' . PHP_EOL;
        }
        $output = \Mix::app()->exec('ps -ef | grep mixhttpd');
        foreach ($output as $item) {
            if (strpos($item, 'mixhttpd master') !== false || strpos($item, 'mixhttpd manager') !== false || strpos($item, 'mixhttpd worker') !== false) {
                echo $item . PHP_EOL;
            }
        }
    }

}
