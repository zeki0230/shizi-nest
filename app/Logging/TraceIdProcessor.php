<?php declare(strict_types=1);

namespace App\Logging;

use Illuminate\Support\Str;
use Monolog\Processor\ProcessorInterface;
use Monolog\ResettableInterface;
use Monolog\LogRecord;

/**
 * Class TraceIdProcessor
 *
 * 该类实现了 Monolog 的处理器接口，用于向日志记录中添加唯一的请求ID。
 * 在处理日志时，每个请求都会生成一个新的请求ID，并将其附加到日志记录的额外信息中。
 * 支持在处理器被重置时生成新的请求ID。
 *
 * @package App\Logging
 * @author Zhao Kun <zhaokun@douyuxingchen.com>
 */
class TraceIdProcessor implements ProcessorInterface, ResettableInterface
{
    private string $traceId;

    /**
     * TraceIdProcessor constructor.
     *
     * 初始化请求ID处理器，并生成一个唯一的请求ID。
     *
     * @author Zhao Kun <zhaokun@douyuxingchen.com>
     */
    public function __construct()
    {

    }

    /**
     * 处理日志记录，向记录中添加请求ID。
     *
     * @param LogRecord $record 日志记录
     * @return LogRecord 返回添加了请求ID的日志记录
     *
     * @author Zhao Kun <zhaokun@douyuxingchen.com>
     */
    public function __invoke(LogRecord $record): LogRecord
    {
        $this->traceId = app()->bound('traceId')
            ? app('traceId')
            : ($this->extractTraceIdFromHeader() ?? $this->generateTraceId());

        if (!app()->bound('traceId')) {
            app()->singleton('traceId', fn() => $this->traceId);
        }
        $record->extra['path'] = $this->getCurrentPathOrJobName();
        $record->extra['traceId'] = $this->traceId;

        return $record;
    }

    /**
     * 重置请求ID，生成新的请求ID。
     *
     * @author Zhao Kun <zhaokun@douyuxingchen.com>
     */
    public function reset(): void
    {
        $this->traceId = $this->generateTraceId();
    }

    /**
     * 获取当前请求ID。
     *
     * @return string 当前请求ID
     *
     * @author Zhao Kun <zhaokun@douyuxingchen.com>
     */
    public function getTraceId(): string
    {
        return $this->traceId;
    }

    /**
     * 生成唯一的请求ID。
     *
     * @return string 生成的请求ID
     *
     * @author Zhao Kun <zhaokun@douyuxingchen.com>
     */
    private function generateTraceId(): string
    {
        return Str::uuid()->toString();
    }

    /**
     * 从请求的 Header 中提取 `traceId`。
     *
     * @return string|null 提取到的 `traceId`，如果不存在则返回 null
     *
     * @author Zhao Kun <zhaokun@douyuxingchen.com>
     */
    private function extractTraceIdFromHeader(): ?string
    {
        $headers = app('request')->headers;
        $headerTraceId = $headers->get('traceId') ?? $headers->get('traceId');
        // 验证提取的 ID 是否符合 UUID 格式
        return $headerTraceId && Str::isUuid($headerTraceId) ? $headerTraceId : null;
    }

    private function getCurrentPathOrJobName(): string
    {
        if (app()->runningInConsole() && app()->bound('jobName')) {
            return 'Job-'. (app('jobName') ? app('jobName') :  'console') ;
        } elseif(php_sapi_name() === 'cli'){
            global $argv;
            $commandName = $argv[1] ?? 'unknown-command';
            return 'Command-'.$commandName;
        } else {
            // HTTP 请求
            return 'Api-'. request()->path() ?? 'unknown-path';
        }
    }

}
