#!/usr/bin/env bash
# Nginx: api.* → Laravel API, app.* → SchoolCRM, campustoday.in → marketing site.
# Run on GCP VM: bash scripts/fix_vm_nginx.sh

set -euo pipefail

API_SITE="${API_SITE:-/etc/nginx/sites-available/campustoday}"
WEB_SITE="${WEB_SITE:-/etc/nginx/sites-available/campustoday-web}"
MARKETING_SITE="${MARKETING_SITE:-/etc/nginx/sites-available/campustoday-marketing}"
API_ROOT="${API_ROOT:-/var/www/campustoday-api/public}"
WEB_ROOT="${WEB_ROOT:-/var/www/campustoday-web}"
MARKETING_ROOT="${MARKETING_ROOT:-/var/www/campustoday-web/marketing}"
API_HOST="${API_HOST:-api.campustoday.in}"
WEB_HOST="${WEB_HOST:-app.campustoday.in}"
ROOT_HOST="${ROOT_HOST:-campustoday.in}"
WWW_HOST="${WWW_HOST:-www.campustoday.in}"
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

echo "==> Web app ($WEB_HOST) -> $WEB_ROOT"
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

echo "==> Marketing ($ROOT_HOST, $WWW_HOST) -> $MARKETING_ROOT"
sudo tee "$MARKETING_SITE" > /dev/null <<EOF
server {
    listen 80;
    server_name ${ROOT_HOST} ${WWW_HOST};
    root ${MARKETING_ROOT};
    index asimos-schoolcrm.html;

    location /assets/ {
        alias ${WEB_ROOT}/assets/;
        access_log off;
        expires 7d;
    }

    location = /index.php {
        return 302 https://${WEB_HOST}/;
    }

    location / {
        try_files \$uri \$uri/ /asimos-schoolcrm.html;
    }
}
EOF

sudo ln -sf "$API_SITE" /etc/nginx/sites-enabled/campustoday
sudo ln -sf "$WEB_SITE" /etc/nginx/sites-enabled/campustoday-web
sudo ln -sf "$MARKETING_SITE" /etc/nginx/sites-enabled/campustoday-marketing
sudo rm -f /etc/nginx/sites-enabled/default

sudo nginx -t
sudo systemctl reload nginx

echo "==> Re-apply HTTPS for: ${API_HOST}, ${WEB_HOST}, ${ROOT_HOST}, ${WWW_HOST}"
if command -v certbot >/dev/null 2>&1; then
  sudo certbot --nginx \
    -d "${API_HOST}" \
    -d "${WEB_HOST}" \
    -d "${ROOT_HOST}" \
    -d "${WWW_HOST}" \
    --non-interactive \
    --agree-tos \
    --redirect \
    --expand \
    -m "${CERTBOT_EMAIL:-meetprasadviswa@gmail.com}" \
    || echo "    Certbot failed — run: sudo certbot --nginx -d ${API_HOST} -d ${WEB_HOST} -d ${ROOT_HOST} -d ${WWW_HOST}"
else
  echo "    Install certbot first."
fi

sudo nginx -t
sudo systemctl reload nginx

echo ""
echo "Verify:"
echo "  curl -sI https://${ROOT_HOST}/ | head -3"
echo "  curl -sI https://${WEB_HOST}/ | head -3"
echo "  curl -sI https://${API_HOST}/v1/health | head -3"
