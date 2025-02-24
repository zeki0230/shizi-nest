<?php

namespace App\Logging;

use Monolog\Formatter\LineFormatter;
use Illuminate\Log\Logger;

class CustomizeFormatter
{
    public function __invoke(Logger $logger): void
    {
        foreach ($logger->getHandlers() as $handler) {
            $handler->setFormatter($this->getCustomFormatter());
            $handler->pushProcessor(new TraceIdProcessor());
        }
    }

    protected function getCustomFormatter(): LineFormatter
    {
        $dateFormat = "Y-m-d H:i:s";
        $output = "[%datetime%] %channel%.%level_name%: %extra.path% %extra.traceId% %context% %message%\n";
        return new LineFormatter($output, $dateFormat, true, true);
    }
}
