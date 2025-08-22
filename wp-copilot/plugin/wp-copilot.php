<?php
/**
 * Plugin Name: WP Copilot (MVP)
 * Description: Copiloto conversacional con GPT-5 o Gemini 2.5 Flash, lectura segura de archivos y logging básico.
 * Version: 0.2.0
 * Author: Pedro + Codex Guide
 */

if (!defined('ABSPATH')) exit;

define('WPCOPILOT_PATH', plugin_dir_path(__FILE__));
define('WPCOPILOT_URL', plugin_dir_url(__FILE__));

require_once WPCOPILOT_PATH . 'includes/class-logger.php';
require_once WPCOPILOT_PATH . 'includes/class-llm.php';
require_once WPCOPILOT_PATH . 'includes/class-files.php';
require_once WPCOPILOT_PATH . 'includes/class-admin.php';
require_once WPCOPILOT_PATH . 'includes/class-router.php';

add_action('init', function() {
  // Shortcode de chat público
  add_shortcode('wp_copilot_chat', function($atts){
    ob_start();
    include WPCOPILOT_PATH.'views/chat-widget.php';
    return ob_get_clean();
  });
});

register_activation_hook(__FILE__, function(){
  \WPCopilot\Logger::ensure_dirs();
});
