<?php
namespace WPCopilot;

if (!defined('ABSPATH')) exit;

class Logger {
  public static function log_dir(): string {
    $upload_dir = wp_upload_dir();
    return trailingslashit($upload_dir['basedir']) . 'wp-copilot/logs';
  }

  public static function ensure_dirs(): void {
    $dir = self::log_dir();
    if (!file_exists($dir)) wp_mkdir_p($dir);
  }

  private static function enabled(): bool {
    $opt = get_option('wpcopilot_logs_enabled', 'yes');
    return $opt === 'yes';
  }

  public static function write(string $level, string $msg, array $context = []): void {
    if (!self::enabled()) return;
    self::ensure_dirs();
    $file = self::log_dir() . '/app.log';
    $line = sprintf(
      "[%s] %s %s %s\n",
      gmdate('c'),
      strtoupper($level),
      $msg,
      $context ? wp_json_encode($context, JSON_UNESCAPED_UNICODE) : ''
    );
    file_put_contents($file, $line, FILE_APPEND);
  }
}
