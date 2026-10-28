#!/usr/bin/env bash
set -Eeuo pipefail
cd /var/www
export PORT="${PORT:-10000}"
if [[ -z "${APP_KEY:-}" ]]; then
    echo 'Falta APP_KEY. Genera una clave una sola vez y configúrala como secreto; no se regenera al arrancar.' >&2
    exit 1
fi
if [[ ! "${PORT:-10000}" =~ ^[0-9]+$ ]] || (( PORT < 1 || PORT > 65535 )); then
    echo 'PORT debe ser un puerto válido.' >&2
    exit 1
fi
if [[ "${APP_ENV:-production}" == production ]]; then export APP_DEBUG=false; fi
if [[ "${RENDER:-false}" == true && "${MEDIA_DRIVER:-local}" != s3 ]]; then
    echo 'Render requiere MEDIA_DRIVER=s3 para conservar los archivos entre reinicios.' >&2
    exit 1
fi
if [[ "${MEDIA_DRIVER:-local}" == s3 ]]; then
    for name in MEDIA_ACCESS_KEY_ID MEDIA_SECRET_ACCESS_KEY MEDIA_REGION MEDIA_ENDPOINT MEDIA_PUBLIC_BUCKET MEDIA_PUBLIC_URL MEDIA_PRIVATE_BUCKET; do
        if [[ -z "${!name:-}" ]]; then echo "Falta configurar $name para el almacenamiento externo." >&2; exit 1; fi
    done
fi
mkdir -p storage/app/public storage/app/private/pedidos storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
# Sólo se sustituye PORT: conservar $uri y las variables propias de Nginx.
envsubst '${PORT}' < /etc/nginx/templates/app.conf.template > /etc/nginx/sites-enabled/default
php artisan config:cache
php artisan view:cache
if [[ "${MEDIA_DRIVER:-local}" != s3 ]]; then php artisan storage:link --force; fi
nginx -t
exec /usr/bin/supervisord -n -c /etc/supervisor/supervisord.conf
