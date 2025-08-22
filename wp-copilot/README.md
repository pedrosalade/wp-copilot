# WP Copilot (MVP) — OpenAI GPT‑5 / Google Gemini 2.5 Flash

Copiloto conversacional para WordPress que permite:
- Chat limpio (shortcode `[wp_copilot_chat]` y panel en admin).
- Lectura **solo lectura** de archivos bajo `public_html` (whitelist y límites).
- Logging a `wp-content/uploads/wp-copilot/logs/app.log`.
- Selector de proveedor: **OpenAI (GPT‑5)** o **Google Gemini 2.5 Flash**.
- API keys configurables por **entorno** o en **Ajustes** del plugin.
- Endpoint REST: `POST /wp-json/wpcopilot/v1/ask` (`{message:string}`) con rate‑limit 5 req/min/IP.

> MVP sin memoria persistente. cURL nativo (sin SDKs externos).

## Requisitos
- PHP 8.0+
- WordPress 6.0+

## Instalación rápida
1. Descomprimí este ZIP. Subí la carpeta `wp-copilot/plugin` como `wp-content/plugins/wp-copilot/`.
2. Activá el plugin en wp-admin.
3. En **Ajustes → WP Copilot** elegí proveedor, modelo y cargá tus API Keys si no usás variables de entorno.
4. Insertá `[wp_copilot_chat]` en una página para el chat.
5. Proba por REST:
   ```bash
   curl -s -X POST -H "Content-Type: application/json" -d '{"message":"Hola"}' https://TU-SITIO/wp-json/wpcopilot/v1/ask
   ```

## Variables de entorno (opcional/recomendado)
- `OPENAI_API_KEY`, `OPENAI_MODEL`, `OPENAI_TEMPERATURE`
- `GEMINI_API_KEY`, `GEMINI_MODEL`

## Seguridad
- Sanitización de inputs, nonces para admin, capabilities `manage_options` para lectura de archivos.
- Rate-limit: 5 req/min por IP.

