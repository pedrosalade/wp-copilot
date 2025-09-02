<?php if (!defined('ABSPATH')) exit; ?>
<div class="wrap">
  <h1>WP Copilot</h1>

  <form method="post" action="options.php" class="wpcopilot-card">
    <?php settings_fields('wpcopilot_group'); ?>
    <h2>Ajustes</h2>
    <div class="settings-grid">
      <div>
        <label class="inline"><strong>Proveedor</strong><br>
          <?php $prov = get_option('wpcopilot_provider','openai'); ?>
          <select name="wpcopilot_provider">
            <option value="openai" <?php selected($prov,'openai'); ?>>OpenAI (GPT‑5)</option>
            <option value="gemini" <?php selected($prov,'gemini'); ?>>Google Gemini 2.5 Flash</option>
          </select>
        </label>

        <label class="inline"><strong>Temperatura (0–1)</strong><br>
          <input type="text" name="wpcopilot_temperature" value="<?php echo esc_attr(get_option('wpcopilot_temperature','0.2')); ?>">
        </label>

        <label class="inline"><strong>Habilitar logs</strong><br>
          <?php $cur = get_option('wpcopilot_logs_enabled','yes'); ?>
          <select name="wpcopilot_logs_enabled">
            <option value="yes" <?php selected($cur,'yes'); ?>>Sí</option>
            <option value="no"  <?php selected($cur,'no'); ?>>No</option>
          </select>
        </label>
      </div>

      <div>
        <label class="inline"><strong>Modelo OpenAI</strong><br>
          <input type="text" name="wpcopilot_openai_model" value="<?php echo esc_attr(get_option('wpcopilot_openai_model','gpt-5')); ?>">
          <small class="help">Usará <code>OPENAI_MODEL</code> del entorno si está seteado.</small>
        </label>

        <label class="inline"><strong>Modelo Gemini</strong><br>
          <input type="text" name="wpcopilot_gemini_model" value="<?php echo esc_attr(get_option('wpcopilot_gemini_model','gemini-2.5-flash')); ?>">
          <small class="help">Usará <code>GEMINI_MODEL</code> del entorno si está seteado.</small>
        </label>

        <label class="inline"><strong>API Key OpenAI (fallback)</strong><br>
          <input type="password" name="wpcopilot_api_key_openai" value="<?php echo esc_attr(get_option('wpcopilot_api_key_openai','')); ?>">
          <small class="help">Preferí usar la variable de entorno <code>OPENAI_API_KEY</code>.</small>
        </label>

        <label class="inline"><strong>API Key Gemini (fallback)</strong><br>
          <input type="password" name="wpcopilot_api_key_gemini" value="<?php echo esc_attr(get_option('wpcopilot_api_key_gemini','')); ?>">
          <small class="help">Preferí usar la variable de entorno <code>GEMINI_API_KEY</code>.</small>
        </label>
      </div>
    </div>
    <?php submit_button('Guardar cambios'); ?>
  </form>

  <div class="wpcopilot-card">
    <h2>Probar Chat</h2>
    <p>Escribí un prompt y envialo al modelo seleccionado. (En admin ya se incluye el nonce.)</p>
    <textarea id="wpcopilot_prompt"></textarea>
    <p><button id="wpcopilot_send" class="button button-primary">Enviar</button></p>
    <div class="wpcopilot-chat" style="border:none;padding:0;">
      <div class="wpcopilot-messages" id="wpcopilot_messages"></div>
    </div>
  </div>
</div>
