<?php
/**
 * Logger - Application logging
 */

namespace App\Core;

class Logger
{
    public const DEBUG = 'DEBUG';
    public const INFO = 'INFO';
    public const WARNING = 'WARNING';
    public const ERROR = 'ERROR';
    public const CRITICAL = 'CRITICAL';

    private const LEVELS = [
        self::DEBUG => 0,
        self::INFO => 1,
        self::WARNING => 2,
        self::ERROR => 3,
        self::CRITICAL => 4,
    ];

    private string $logFile;
    private string $minLevel;
    private ?string $requestId = null;

    public function __construct(string $logFile, string $minLevel = self::DEBUG)
    {
        $this->logFile = $logFile;
        $this->minLevel = $minLevel;

        // Ensure log directory exists
        $dir = dirname($logFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
    }

    /**
     * Set request ID for all log entries
     */
    public function setRequestId(string $requestId): void
    {
        $this->requestId = $requestId;
    }

    /**
     * Log debug message
     */
    public function debug(string $message, array $context = []): void
    {
        $this->log(self::DEBUG, $message, $context);
    }

    /**
     * Log info message
     */
    public function info(string $message, array $context = []): void
    {
        $this->log(self::INFO, $message, $context);
    }

    /**
     * Log warning message
     */
    public function warning(string $message, array $context = []): void
    {
        $this->log(self::WARNING, $message, $context);
    }

    /**
     * Log error message
     */
    public function error(string $message, array $context = []): void
    {
        $this->log(self::ERROR, $message, $context);
    }

    /**
     * Log critical message
     */
    public function critical(string $message, array $context = []): void
    {
        $this->log(self::CRITICAL, $message, $context);
    }

    /**
     * Log message
     */
    public function log(string $level, string $message, array $context = []): void
    {
        // Check if level should be logged
        if (!$this->shouldLog($level)) {
            return;
        }

        $timestamp = date('Y-m-d H:i:s');
        $requestId = $this->requestId ? "[{$this->requestId}]" : '';

        // Interpolate context into message
        $message = $this->interpolate($message, $context);

        // Format context
        $contextStr = '';
        if (!empty($context)) {
            $contextStr = ' ' . json_encode($context, JSON_UNESCAPED_UNICODE);
        }

        $entry = "[{$timestamp}] {$requestId} [{$level}] {$message}{$contextStr}" . PHP_EOL;

        // Write to file
        $this->write($entry);
    }

    /**
     * Check if level should be logged
     */
    private function shouldLog(string $level): bool
    {
        $levelNum = self::LEVELS[$level] ?? 0;
        $minLevelNum = self::LEVELS[$this->minLevel] ?? 0;
        return $levelNum >= $minLevelNum;
    }

    /**
     * Interpolate context values into message
     */
    private function interpolate(string $message, array $context): string
    {
        $replace = [];
        foreach ($context as $key => $value) {
            if (is_string($value) || is_numeric($value)) {
                $replace['{' . $key . '}'] = $value;
            }
        }
        return strtr($message, $replace);
    }

    /**
     * Write to log file
     */
    private function write(string $entry): void
    {
        file_put_contents($this->logFile, $entry, FILE_APPEND | LOCK_EX);
    }

    /**
     * Get last N lines from log
     */
    public function tail(int $lines = 100, ?string $level = null): array
    {
        if (!file_exists($this->logFile)) {
            return [];
        }

        $content = file($this->logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if ($level) {
            $content = array_filter($content, function ($line) use ($level) {
                return str_contains($line, "[{$level}]");
            });
        }

        return array_slice($content, -$lines);
    }

    /**
     * Get log file path
     */
    public function getLogFile(): string
    {
        return $this->logFile;
    }

    /**
     * Get log file size
     */
    public function getLogSize(): int
    {
        if (!file_exists($this->logFile)) {
            return 0;
        }
        return filesize($this->logFile);
    }

    /**
     * Clear log file
     */
    public function clear(): void
    {
        if (file_exists($this->logFile)) {
            file_put_contents($this->logFile, '');
        }
    }

    /**
     * Log exception
     */
    public function exception(\Throwable $e, array $context = []): void
    {
        $context['exception'] = get_class($e);
        $context['file'] = $e->getFile();
        $context['line'] = $e->getLine();
        $context['trace'] = $e->getTraceAsString();

        $this->error($e->getMessage(), $context);
    }
}
