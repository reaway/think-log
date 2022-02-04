# think-log

## 安装
```bash
composer require reaway/think-log
```

## 用法
```php
use Think\Component\Log\Facade\Log;

require __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

Log::setConfig([
    // 默认日志记录通道
    'default' => 'file',
    // 日志记录级别
    'level' => [],
    // 日志类型记录的通道 ['error'=>'email',...]
    'type_channel' => [],
    // 关闭全局日志写入
    'close' => false,
    // 全局日志处理 支持闭包
    'processor' => null,

    // 日志通道列表
    'channels' => [
        'file' => [
            // 日志记录方式
            'type' => 'File',
            // 日志保存目录
            'path' => __DIR__ . DIRECTORY_SEPARATOR . 'log',
            // 单文件日志写入
            'single' => false,
            // 独立日志级别
            'apart_level' => [],
            // 最大日志文件数量
            'max_files' => 0,
            // 使用JSON格式记录
            'json' => false,
            // 日志处理
            'processor' => null,
            // 关闭通道日志写入
            'close' => false,
            // 日志输出格式化
            'format' => '[%s][%s] %s',
            // 是否实时写入
            'realtime_write' => false,
        ],
        // 其它日志通道配置
    ],
]);

Log::record('测试日志信息，这是警告级别', 'notice');
Log::write('测试日志信息，这是警告级别，并且实时写入', 'notice');
```

## 文档

详细参考 [日志处理](https://www.kancloud.cn/manual/thinkphp6_0/1037616)