<?php

namespace mix\web;

use mix\base\Component;

/**
 * App类
 * @author 刘健 <coder.liu@qq.com>
 */
class Application extends \mix\base\Application
{

    /**
     * 执行功能 (Apache/PHP-FPM)
     */
    public function run()
    {
        \Mix::app()->error->register();
        $server  = \Mix::app()->request->server();
        $method  = strtoupper($server['request_method']);
        $action  = empty($server['path_info']) ? '' : substr($server['path_info'], 1);
        $content = $this->runAction($method, $action);
        \Mix::app()->response->setContent($content);
        \Mix::app()->response->send();
        $this->cleanComponents();
    }

    /**
     * 获取组件
     * @param  string $name
     */
    public function __get($name)
    {
        // 返回单例
        if (isset($this->_components[$name])) {
            // 触发请求开始事件
            if ($this->_components[$name]->getStatus() == Component::STATUS_READY) {
                $this->_components[$name]->onRequestStart();
            }
            // 返回对象
            return $this->_components[$name];
        }
        // 装载组件
        $this->loadComponent($name);
        // 触发请求开始事件
        $this->_components[$name]->onRequestStart();
        // 返回对象
        return $this->_components[$name];
    }

    /**
     * 装载全部组件
     */
    public function loadAllComponent()
    {
        foreach (array_keys($this->register) as $name) {
            $this->loadComponent($name);
        }
    }

    /**
     * 清扫组件容器
     * 只清扫 STATUS_RUNNING 状态的组件
     */
    public function cleanComponents()
    {
        foreach ($this->_components as $component) {
            if ($component->getStatus() == Component::STATUS_RUNNING) {
                $component->onRequestEnd();
            }
        }
    }

    /**
     * 获取公开目录路径
     * @return string
     */
    public function getPublicPath()
    {
        return $this->basePath . 'public' . DIRECTORY_SEPARATOR;
    }

    /**
     * 获取视图目录路径
     * @return string
     */
    public function getViewPath()
    {
        return $this->basePath . 'view' . DIRECTORY_SEPARATOR;
    }

}
