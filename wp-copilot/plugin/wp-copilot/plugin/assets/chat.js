(function($){
  function el(sel){ return $(sel); }
  function typing(on){
    let $t = $('.typing');
    if(on){
      if($t.length===0){ $('#wpcopilot_messages').append('<div class="typing">Escribiendo…</div>'); }
    } else {
      $t.remove();
    }
  }
  function appendMsg(role, text){
    typing(false);
    const $m = $('<div class="wpcopilot-msg">').addClass(role).text(text);
    const $box = $('#wpcopilot_messages');
    $box.append($m);
    $box.scrollTop($box[0].scrollHeight);
  }

  async function ask(msg, rest, nonce){
    const headers = {'Content-Type':'application/json'};
    if (nonce) headers['X-WP-Nonce'] = nonce;
    const r = await fetch(rest, {
      method:'POST',
      headers,
      body: JSON.stringify({message: msg})
    });
    if(!r.ok){
      let detail = 'HTTP ' + r.status;
      try { const j = await r.json(); if (j && j.message) detail += ' - ' + j.message; } catch(_){}
      throw new Error(detail);
    }
    return r.json();
  }

  async function sendMsg(getCtx){
    const $focus = $('#wpcopilot_input:focus, #wpcopilot_prompt:focus');
    const val = $focus.val()
      || $('#wpcopilot_input').val()
      || $('#wpcopilot_prompt').val();
    if(!val) return;

    appendMsg('user', val);
    $('#wpcopilot_input').val('');

    const {rest, nonce} = getCtx();
    try{
      typing(true);
      const res = await ask(val, rest, nonce);
      appendMsg('ai', res.answer || '(sin respuesta)');
    }catch(e){
      appendMsg('ai', 'Error: ' + e.message);
    } finally {
      typing(false);
    }
  }

  // Front
  $(document).on('click','#wpcopilot_go', ()=> sendMsg(()=> window.WPCOPILOT_PUBLIC || {rest: ''}));
  // Admin
  $(document).on('click','#wpcopilot_send', ()=> sendMsg(()=> ({rest: WPCOPILOT.rest, nonce: WPCOPILOT.nonce})));

  // Ctrl+Enter para enviar
  $(document).on('keydown', '#wpcopilot_input, #wpcopilot_prompt', function(e){
    if (e.ctrlKey && e.key === 'Enter') {
      e.preventDefault();
      if (this.id === 'wpcopilot_input') $('#wpcopilot_go').trigger('click');
      else $('#wpcopilot_send').trigger('click');
    }
  });
})(jQuery);
