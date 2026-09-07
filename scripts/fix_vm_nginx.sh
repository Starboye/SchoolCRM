#!/usr/bin/env bash
# Fix nginx so api.campustoday.in → Laravel and app.campustoday.in → SchoolCRM web.
# Run on GCP VM: bash scripts/fix_vm_nginx.sh

set -euo pipefail

API_SITE="${API_SITE:-/etc/nginx/sites-available/campustoday}"
WEB_SITE="${WEB_SITE:-/etc/nginx/sites-available/campustoday-web}"
API_ROOT="${API_ROOT:-/var/www/campustoday-api/public}"
WEB_ROOT="${WEB_ROOT:-/var/www/campustoday-web}"
API_HOST="${API_HOST:-api.campustoday.in}"
WEB_HOST="${WEB_HOST:-app.campustoday.in}"
PHP_FPM_SOCK="${PHP_FPM_SOCK:-$(ls /run/php/php*-fpm.sock 2>/dev/null | head -1)}"

if [[ -z "$PHP_FPM_SOCK" ]]; then
  echo "Error: no php-fpm socket under /run/php/" >&2
  exit 1
fi

echo "==> API site ($API_HOST) -> $API_ROOT"
sudo tee "$API_SITE" > /dev/null <<EOF
server {
    listen 80;
    server_name ${API_HOST};
    root ${API_ROOT};
    index index.php;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \\.php\$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:${PHP_FPM_SOCK};
    }
}
EOF

echo "==> Web site ($WEB_HOST) -> $WEB_ROOT"
sudo tee "$WEB_SITE" > /dev/null <<EOF
server {
    listen 80;
    server_name ${WEB_HOST};
    root ${WEB_ROOT};
    index index.php;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \\.php\$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:${PHP_FPM_SOCK};
    }

    location ~ /\\.(env|git) {
        deny all;
    }
}
EOF

sudo ln -sf "$API_SITE" /etc/nginx/sites-enabled/campustoday
sudo ln -sf "$WEB_SITE" /etc/nginx/sites-enabled/campustoday-web
sudo rm -f /etc/nginx/sites-enabled/default

sudo nginx -t
sudo systemctl reload nginx

echo "==> Re-apply HTTPS (required — otherwise app.* HTTPS may hit Laravel)"
if command -v certbot >/dev/null 2>&1; then
  sudo certbot --nginx \
    -d "${API_HOST}" \
    -d "${WEB_HOST}" \
    --non-interactive \
    --agree-tos \
    --redirect \
    --expand \
    -m "${CERTBOT_EMAIL:-meetprasadviswa@gmail.com}" \
    || echo "    Certbot failed — run manually: sudo certbot --nginx -d ${API_HOST} -d ${WEB_HOST}"
else
  echo "    Install certbot, then: sudo certbot --nginx -d ${API_HOST} -d ${WEB_HOST}"
fi

sudo nginx -t
sudo systemctl reload nginx

echo ""
echo "Verify:"
echo "  curl -sI https://${WEB_HOST}/ | head -3"
echo "  curl -sI https://${API_HOST}/v1/health | head -3"
curl -sI -H "Host: ${WEB_HOST}" http://127.0.0.1/ | head -3 || true
