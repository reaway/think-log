<?php
declare (strict_types=1);

use Think\Component\Log\Facade\LogFacade;

if (!function_exists('trace')) {
    /**
     * 记录日志信息
     * @param mixed $log log信息 支持字符串和数组
     * @param string $level 日志级别
     * @return array|void
     */
    function trace($log = '[think]', string $level = 'log')
    {
        if ('[think]' === $log) {
            return Facade::getLog();
        }

        LogFacade::record($log, $level);
    }
}