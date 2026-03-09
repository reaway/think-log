<?php
declare(strict_types=1);

namespace Think\Component\Log;

use Think\Component\Log\Log;

/**
 * Class ChannelSet
 * @package Log
 * @mixin Channel
 */
class ChannelSet
{
    public function __construct(protected Log $log, protected array $channels)
    {
    }

    public function __call($method, $arguments)
    {
        foreach ($this->channels as $channel) {
            $this->log->channel($channel)->{$method}(...$arguments);
        }
    }
}
