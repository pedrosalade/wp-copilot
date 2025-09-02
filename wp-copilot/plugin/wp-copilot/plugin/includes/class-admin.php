<?php
namespace WPCopilot;

if (!defined('ABSPATH')) exit;

class Admin {
  public static function register(): void {
    add_action('admin_menu', [self::class, 'menu']);
    add_action('admin_init', [self::class, 'settings']);
    add_action('admin_enqueue_scripts', [self::class, 'assets']);
  }

  public static function menu(): void {
    add_menu_page(
      'WP Copilot',
      'WP Copilot',
      'manage_options',
      'wpcopilot',
      [self::class,'render'],
      'dashicons-format-chat',
      65
    );
  }

  public static function settings(): void {
    // Logs
    register_setting('wpcopilot_group', 'wpcopilot_logs_enabled', [
      'type' => 'string',
      'sanitize_callback' => function($v){ return ($v === 'no') ? 'no' : 'yes'; },
      'default' => 'yes'
    ]);

    // Provider + model + temp
    register_setting('wpcopilot_group', 'wpcopilot_provider', [
      'type' => 'string',
      'sanitize_callback' => function($v){ return in_array($v, ['openai','gemini'], true) ? $v : 'openai'; },
      'default' => 'openai'
    ]);
    register_setting('wpcopilot_group', 'wpcopilot_temperature', [
      'type' => 'string',
      'sanitize_callback' => function($v){ $n = floatval($v); return (string) ( $n >= 0 && $n <= 1 ? $n : 0.2 ); },
      'default' => '0.2'
    ]);

    register_setting('wpcopilot_group', 'wpcopilot_openai_model', [
      'type' => 'string', 'sanitize_callback' => 'sanitize_text_field', 'default' => 'gpt-5'
    ]);
    register_setting('wpcopilot_group', 'wpcopilot_gemini_model', [
      'type' => 'string', 'sanitize_callback' => 'sanitize_text_field', 'default' => 'gemini-2.5-flash'
    ]);

    // API Keys (fallbacks)
    register_setting('wpcopilot_group', 'wpcopilot_api_key_openai', [
      'type' => 'string', 'sanitize_callback' => 'sanitize_text_field', 'default' => ''
    ]);
    register_setting('wpcopilot_group', 'wpcopilot_api_key_gemini', [
      'type' => 'string', 'sanitize_callback' => 'sanitize_text_field', 'default' => ''
    ]);
  }

  public static function assets($hook): void {
    if ($hook !== 'toplevel_page_wpcopilot') return;
    wp_enqueue_style('wpcopilot-admin', WPCOPILOT_URL.'assets/admin.css', [], '0.2.0');
    wp_enqueue_style('wpcopilot-chat', WPCOPILOT_URL.'assets/chat.css', [], '0.2.0');
    wp_enqueue_script('wpcopilot-chat', WPCOPILOT_URL.'assets/chat.js', ['jquery'], '0.2.0', true);
    wp_localize_script('wpcopilot-chat', 'WPCOPILOT', [
      'rest'  => esc_url_raw( rest_url('wpcopilot/v1/ask') ),
      'nonce' => wp_create_nonce('wp_rest')
    ]);
  }

  public static function render(): void {
    include WPCOPILOT_PATH.'views/admin-page.php';
  }
}
Admin::register();
