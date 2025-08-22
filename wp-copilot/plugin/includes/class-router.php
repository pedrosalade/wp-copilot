<?php
namespace WPCopilot;

if (!defined('ABSPATH')) exit;

class Router {

  private static function client_ip(): string {
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
  }

  private static function rate_key(): string {
    $ip = self::client_ip();
    return 'wpcopilot_rate_' . md5($ip);
  }

  private static function rate_ok(): bool {
    $key = self::rate_key();
    $hits = (int) get_transient($key);
    if ($hits >= 5) return false; // 5 req/min/IP
    set_transient($key, $hits + 1, MINUTE_IN_SECONDS);
    return true;
  }

  private static function verify_nonce_if_needed(\WP_REST_Request $req): void {
    if (is_user_logged_in()) {
      $nonce = $req->get_header('X-WP-Nonce') ?: $req->get_header('x-wp-nonce');
      if (!$nonce || !wp_verify_nonce($nonce, 'wp_rest')) {
        throw new \Exception('Nonce inválido', 403);
      }
    }
  }

  public static function register(): void {
    add_action('rest_api_init', function(){
      // /ask (POST)
      register_rest_route('wpcopilot/v1', '/ask', [
        'methods'  => 'POST',
        'permission_callback' => '__return_true',
        'callback' => function(\WP_REST_Request $req){
          try {
            if (!self::rate_ok()) {
              return new \WP_Error('rate_limited','Demasiadas solicitudes. Probá nuevamente en 1 minuto.', ['status'=>429]);
            }
            self::verify_nonce_if_needed($req);

            $message = $req->get_param('message');
            if (!is_string($message)) {
              return new \WP_Error('bad_request','\"message\" debe ser string', ['status'=>400]);
            }
            $message = sanitize_textarea_field($message);
            if ($message === '') {
              return new \WP_Error('bad_request','\"message\" requerido', ['status'=>400]);
            }

            $answer = LLM::ask($message);
            return ['ok'=>true, 'answer'=>$answer];

          } catch (\Exception $e) {
            $code = is_int($e->getCode()) && $e->getCode() >= 300 ? $e->getCode() : 500;
            Logger::write('ERROR','ask.fail',['err'=>$e->getMessage()]);
            return new \WP_Error('server_error', $e->getMessage(), ['status'=>$code]);
          }
        }
      ]);

      // /files (GET) - lista (solo admin)
      register_rest_route('wpcopilot/v1', '/files', [
        'methods'  => 'GET',
        'permission_callback' => function(){ return current_user_can('manage_options'); },
        'callback' => function(\WP_REST_Request $req){
          try {
            self::verify_nonce_if_needed($req);
            return Files::list_public_html();
          } catch (\Exception $e) {
            return new \WP_Error('server_error',$e->getMessage(),['status'=>500]);
          }
        }
      ]);

      // /file?path=/relativo (GET) - leer (solo admin)
      register_rest_route('wpcopilot/v1', '/file', [
        'methods'  => 'GET',
        'permission_callback' => function(){ return current_user_can('manage_options'); },
        'callback' => function(\WP_REST_Request $req){
          try {
            self::verify_nonce_if_needed($req);
            $p = (string) $req->get_param('path');
            $p = sanitize_text_field($p);
            if ($p === '') return new \WP_Error('bad_request','\"path\" requerido',['status'=>400]);
            return ['path'=>$p, 'content'=>Files::read($p)];
          } catch (\Exception $e) {
            return new \WP_Error('server_error',$e->getMessage(),['status'=>500]);
          }
        }
      ]);
    });
  }
}
Router::register();
