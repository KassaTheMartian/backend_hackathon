<?php

namespace App\Logging;

use Monolog\Formatter\LineFormatter;
use Monolog\LogRecord;

class DailyJsonFormatter extends LineFormatter
{
    public function format(LogRecord $record): string
    {
        // Format: timestamp level: message {context}
        $timestamp = $record->datetime->format('Y-m-d H:i:s.u');
        $level = strtoupper($record->level->getName());
        $message = $record->message;
        
        $formatted = [
            'timestamp' => $timestamp,
            'level' => $level,
            'message' => $message,
        ];

        // Add context if exists
        if (!empty($record->context)) {
            $formatted['context'] = $record->context;
        }

        // Add extra if exists
        if (!empty($record->extra)) {
            $formatted['extra'] = $record->extra;
        }

        return json_encode($formatted, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n";
    }
}
