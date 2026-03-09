<?php
declare(strict_types=1);

namespace Think\Component\Log;

use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;
use Stringable;
use think\helper\Arr;
use Think\Component\Log\Channel;
use Think\Component\Log\ChannelSet;
use Think\Component\Manager\Manager;
use Think\Component\Config\Config;

/**
 * 日志管理类
 * @package think
 * @mixin Channel
 */
class Log extends Manager implements LoggerInterface
{
    use LoggerTrait;

    const EMERGENCY = 'emergency';
    const ALERT = 'alert';
    const CRITICAL = 'critical';
    const ERROR = 'error';
    const WARNING = 'warning';
    const NOTICE = 'notice';
    const INFO = 'info';
    const DEBUG = 'debug';
    const SQL = 'sql';

    protected $namespace = '\\Think\\Component\\Log\\Driver\\';

    /**
     * 配置参数
     * @var array
     */
    protected $config = [
        // 默认日志记录通道
        'default'      => 'file',
        // 日志记录级别
        'level'        => [],
        // 日志类型记录的通道 ['error'=>'email',...]
        'type_channel' => [],
        // 关闭全局日志写入
        'close'        => false,
        // 全局日志处理 支持闭包
        'processor'    => null,

        // 日志通道列表
        'channels'     => [
            'file' => [
                // 日志记录方式
                'type'           => 'File',
                // 日志保存目录
                'path'           => '',
                // 单文件日志写入
                'single'         => false,
                // 独立日志级别
                'apart_level'    => [],
                // 最大日志文件数量
                'max_files'      => 0,
                // 使用JSON格式记录
                'json'           => false,
                // 日志处理
                'processor'      => null,
                // 关闭通道日志写入
                'close'          => false,
                // 日志输出格式化
                'format'         => '[%s][%s] %s',
                // 是否实时写入
                'realtime_write' => false,
            ],
            // 其它日志通道配置
        ],
    ];

    public static function __make(Config $config)
    {
        return new static($config->get('log'));
    }

    /**
     * 默认驱动
     * @return string|null
     */
    public function getDefaultDriver(): ?string
    {
        return $this->getConfig('default');
    }

    /**
     * 获取渠道配置
     * @param string $channel
     * @param string $name
     * @param mixed  $default
     * @return array
     */
    public function getChannelConfig(string $channel, ?string $name = null, $default = null)
    {
        if ($config = $this->config['channels'][$channel] ?? null) {
            return Arr::get($config, $name, $default);
        }

        throw new InvalidArgumentException("Channel [$channel] not found.");
    }

    /**
     * driver()的别名
     * @param string|array $name 渠道名
     * @return Channel|ChannelSet
     */
    public function channel(string|array|null $name = null)
    {
        if (is_array($name)) {
            return new ChannelSet($this, $name);
        }

        return $this->driver($name);
    }

    protected function resolveType(string $name)
    {
        return $this->getChannelConfig($name, 'type', 'file');
    }

    public function createDriver(string $name)
    {
        $driver = parent::createDriver($name);

        $lazy = !$this->getChannelConfig($name, "realtime_write", false);
        $allow = array_merge($this->getConfig("level", []), $this->getChannelConfig($name, "level", []));

        return new Channel($name, $driver, $allow, $lazy);
    }

    protected function resolveConfig(string $name)
    {
        return $this->getChannelConfig($name);
    }

    /**
     * 清空日志信息
     * @access public
     * @param string|array $channel 日志通道名
     * @return $this
     */
    public function clear(string|array $channel = '*')
    {
        if ('*' == $channel) {
            $channel = array_keys($this->drivers);
        }

        $this->channel($channel)->clear();

        return $this;
    }

    /**
     * 关闭本次请求日志写入
     * @access public
     * @param string|array $channel 日志通道名
     * @return $this
     */
    public function close(string|array $channel = '*')
    {
        if ('*' == $channel) {
            $channel = array_keys($this->drivers);
        }

        $this->channel($channel)->close();

        return $this;
    }

    /**
     * 获取日志信息
     * @access public
     * @param string $channel 日志通道名
     * @return array
     */
    public function getLog(?string $channel = null): array
    {
        return $this->channel($channel)->getLog();
    }

    /**
     * 保存日志信息
     * @access public
     * @return bool
     */
    public function save(): bool
    {
        /** @var Channel $channel */
        foreach ($this->drivers as $channel) {
            $channel->save();
        }

        return true;
    }

    /**
     * 记录日志信息
     * @access public
     * @param mixed  $msg     日志信息
     * @param string $type    日志级别
     * @param array  $context 替换内容
     * @param bool   $lazy
     * @return $this
     */
    public function record($msg, string $type = 'info', array $context = [], bool $lazy = true)
    {
        $channel = $this->getConfig('type_channel.' . $type);

        $this->channel($channel)->record($msg, $type, $context, $lazy);

        return $this;
    }

    /**
     * 实时写入日志信息
     * @access public
     * @param mixed  $msg     调试信息
     * @param string $type    日志级别
     * @param array  $context 替换内容
     * @return $this
     */
    public function write($msg, string $type = 'info', array $context = [])
    {
        return $this->record($msg, $type, $context, false);
    }

    /**
     * 记录日志信息
     * @access public
     * @param mixed $level   日志级别
     * @param string|Stringable   $message 日志信息
     * @param array  $context 替换内容
     * @return void
     */
    public function log($level, $message, array $context = []): void
    {
        $this->record($message, $level, $context);
    }

    /**
     * 记录sql信息
     * @access public
     * @param string|Stringable  $message 日志信息
     * @param array $context 替换内容
     * @return void
     */
    public function sql($message, array $context = []): void
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    public function __call($method, $parameters)
    {
        $this->log($method, ...$parameters);
    }
}
