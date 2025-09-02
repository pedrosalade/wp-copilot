<?php if (!defined('ABSPATH')) exit; ?>
<div class="wpcopilot-chat">
  <div class="wpcopilot-messages" id="wpcopilot_messages"></div>
  <div class="wpcopilot-input">
    <input type="text" id="wpcopilot_input" placeholder="Escribí tu mensaje..." />
    <button id="wpcopilot_go">Enviar</button>
  </div>
</div>
<link rel="stylesheet" href="<?php echo esc_url( WPCOPILOT_URL.'assets/chat.css'); ?>">
<script src="<?php echo esc_url( WPCOPILOT_URL.'assets/chat.js'); ?>"></script>
<script>
  (function(){
    const rest = "<?php echo esc_url( rest_url('wpcopilot/v1/ask') ); ?>";
    window.WPCOPILOT_PUBLIC = { rest: rest };
  })();
</script>
