<?php
namespace WPCopilot;

if (!defined('ABSPATH')) exit;

class LLM {
  private static function opt(string $name, $default='') {
    $v = get_option($name, $default);
    return is_string($v) ? $v : $default;
  }

  private static function openai_key(): ?string {
    $env = getenv('OPENAI_API_KEY');
    if ($env && strlen($env) > 10) return $env;
    $opt = self::opt('wpcopilot_api_key_openai', '');
    return strlen($opt) > 10 ? $opt : null;
  }

  private static function gemini_key(): ?string {
    $env = getenv('GEMINI_API_KEY');
    if ($env && strlen($env) > 10) return $env;
    $opt = self::opt('wpcopilot_api_key_gemini', '');
    return strlen($opt) > 10 ? $opt : null;
  }

  private static function temperature(): float {
    $t = getenv('OPENAI_TEMPERATURE');
    if ($t !== false && $t !== null && $t !== '') return floatval($t);
    $opt = self::opt('wpcopilot_temperature', '0.2');
    return floatval($opt);
  }

  public static function ask(string $message): string {
    $provider = self::opt('wpcopilot_provider', 'openai');
    if ($provider === 'gemini') {
      return self::ask_gemini($message);
    }
    return self::ask_openai($message);
  }

  private static function ask_openai(string $message): string {
    $apiKey = self::openai_key();
    if (!$apiKey) throw new \Exception('OPENAI_API_KEY no configurado (entorno u opción en WP).');

    $model = getenv('OPENAI_MODEL') ?: self::opt('wpcopilot_openai_model', 'gpt-5');
    $endpoint = 'https://api.openai.com/v1/chat/completions';

    $payload = [
      'model' => $model,
      'temperature' => self::temperature(),
      'messages' => [
        ['role' => 'system', 'content' => 'Sos un copiloto para WordPress. Respondé con detalle, seguridad y claridad.'],
        ['role' => 'user', 'content' => $message],
      ],
    ];

    $ch = curl_init($endpoint);
    curl_setopt_array($ch, [
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey,
      ],
      CURLOPT_POST => true,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_POSTFIELDS => wp_json_encode($payload, JSON_UNESCAPED_UNICODE),
    ]);

    $res = curl_exec($ch);
    if ($res === false) { $err = curl_error($ch); curl_close($ch); throw new \Exception('Error de red: ' . $err); }
    $code = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);

    $json = json_decode($res, true);
    if ($code >= 400) {
      $detail = $json['error']['message'] ?? 'Error desconocido';
      throw new \Exception('OpenAI ' . $code . ': ' . $detail, $code);
    }
    $answer = $json['choices'][0]['message']['content'] ?? '';
    Logger::write('INFO', 'llm.openai.ask', ['len'=>strlen($message), 'code'=>$code, 'model'=>$model]);
    return $answer ?: '(sin contenido)';
  }

  private static function ask_gemini(string $message): string {
    $apiKey = self::gemini_key();
    if (!$apiKey) throw new \Exception('GEMINI_API_KEY no configurado (entorno u opción en WP).');

    $model = getenv('GEMINI_MODEL') ?: self::opt('wpcopilot_gemini_model', 'gemini-2.5-flash');
    $endpoint = 'https://generativelanguage.googleapis.com/v1beta/models/' . rawurlencode($model) . ':generateContent';

    $payload = [
      'contents' => [
        [
          'role' => 'user',
          'parts' => [['text' => $message]],
        ]
      ],
      'generationConfig' => [
        'temperature' => self::temperature(),
      ],
    ];

    $ch = curl_init($endpoint);
    curl_setopt_array($ch, [
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_HTTPHEADER => [
        'Content-Type: application/json; charset=utf-8',
        'x-goog-api-key: ' . $apiKey,
      ],
      CURLOPT_POST => true,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_POSTFIELDS => wp_json_encode($payload, JSON_UNESCAPED_UNICODE),
    ]);

    $res = curl_exec($ch);
    if ($res === false) { $err = curl_error($ch); curl_close($ch); throw new \Exception('Error de red: ' . $err); }
    $code = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);

    $json = json_decode($res, true);
    if ($code >= 400) {
      $detail = $json['error']['message'] ?? (isset($json['error']) ? wp_json_encode($json['error']) : 'Error desconocido');
      throw new \Exception('Gemini ' . $code . ': ' . $detail, $code);
    }

    // Extraer texto
    $answer = '';
    if (isset($json['candidates'][0]['content']['parts'])) {
      foreach ($json['candidates'][0]['content']['parts'] as $p) {
        if (isset($p['text'])) $answer .= $p['text'];
      }
    }
    if ($answer === '' && isset($json['text'])) $answer = $json['text'];

    Logger::write('INFO', 'llm.gemini.ask', ['len'=>strlen($message), 'code'=>$code, 'model'=>$model]);
    return $answer !== '' ? $answer : '(sin contenido)';
  }
}
