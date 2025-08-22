<?php
namespace WPCopilot;

if (!defined('ABSPATH')) exit;

class Files {
  private static array $allow_ext = ['php','js','css','html','md','json','txt'];
  private static int $max_bytes = 256 * 1024; // 256KB
  private static int $max_items = 5000;

  private static function root(): string {
    return rtrim(ABSPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
  }

  public static function list_public_html(): array {
    $root = self::root();
    $rii = new \RecursiveIteratorIterator(
      new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS)
    );
    $out = [];
    foreach ($rii as $file) {
      /** @var \SplFileInfo $file */
      if ($file->isDir()) continue;
      $ext = strtolower(pathinfo($file->getFilename(), PATHINFO_EXTENSION));
      if (!in_array($ext, self::$allow_ext, true)) continue;
      if ($file->getSize() > self::$max_bytes) continue;

      $rel = ltrim(str_replace($root, '', $file->getPathname()), '/\\');
      if (str_starts_with($rel, 'wp-content/uploads/')) continue;

      $out[] = ['path' => '/' . str_replace('\\', '/', $rel), 'size' => (int)$file->getSize()];
      if (count($out) >= self::$max_items) break;
    }
    Logger::write('INFO','files.list', ['count'=>count($out)]);
    return $out;
  }

  public static function read(string $relative): string {
    $relative = '/' . ltrim($relative, '/\\');
    $root = self::root();
    $target = realpath($root . ltrim($relative, '/'));
    $rootReal = realpath($root);

    if (!$target || !$rootReal || !str_starts_with($target, $rootReal)) {
      throw new \Exception('Ruta inválida');
    }
    $ext = strtolower(pathinfo($target, PATHINFO_EXTENSION));
    if (!in_array($ext, self::$allow_ext, true)) throw new \Exception('Extensión no permitida');
    $size = filesize($target);
    if ($size === false || $size > self::$max_bytes) throw new \Exception('Archivo demasiado grande');

    $content = file_get_contents($target);
    if ($content === false) throw new \Exception('No se pudo leer el archivo');

    Logger::write('INFO','files.read', ['path'=>$relative,'bytes'=>strlen($content)]);
    return $content;
  }
}
