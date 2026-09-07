#!/usr/bin/env bash
# Deploy SchoolCRM web app from GitHub onto the GCP VM (campustoday-api).
# Run on the VM via browser SSH after DNS points app.campustoday.in to this host.
#
# Usage:
#   curl -fsSL https://raw.githubusercontent.com/Starboye/SchoolCRM/main/scripts/deploy_vm_web.sh | bash
#   # or, after git clone:
#   ./scripts/deploy_vm_web.sh
#
# Prerequisites: nginx, php-fpm, mariadb, git. API already at /var/www/campustoday-api.

set -euo pipefail

REPO_URL="${REPO_URL:-https://github.com/Starboye/SchoolCRM.git}"
WEB_ROOT="${WEB_ROOT:-/var/www/campustoday-web}"
CLONE_DIR="${CLONE_DIR:-/home/meetprasadviswa/SchoolCRM}"
NGINX_SITE="${NGINX_SITE:-/etc/nginx/sites-available/campustoday-web}"
SERVER_NAME="${SERVER_NAME:-app.campustoday.in}"
PHP_FPM_SOCK="${PHP_FPM_SOCK:-}"

if [[ -z "$PHP_FPM_SOCK" ]]; then
  PHP_FPM_SOCK="$(ls /run/php/php*-fpm.sock 2>/dev/null | head -1 || true)"
fi
if [[ -z "$PHP_FPM_SOCK" ]]; then
  echo "Error: no php-fpm socket found under /run/php/" >&2
  exit 1
fi

echo "==> Pull latest from $REPO_URL"
if [[ -d "$CLONE_DIR/.git" ]]; then
  git -C "$CLONE_DIR" pull --ff-only origin main
else
  git clone --depth 1 "$REPO_URL" "$CLONE_DIR"
fi

echo "==> Sync to $WEB_ROOT"
sudo mkdir -p "$WEB_ROOT"
sudo rsync -a --delete \
  --exclude '.git' \
  --exclude 'node_modules' \
  --exclude '.env' \
  "$CLONE_DIR"/ "$WEB_ROOT"/
sudo chown -R meetprasadviswa:www-data "$WEB_ROOT"

if [[ ! -f "$WEB_ROOT/.env" ]]; then
  echo "==> Creating .env from .env.example (edit DB_PASSWORD on server)"
  sudo cp "$WEB_ROOT/.env.example" "$WEB_ROOT/.env"
  sudo chown meetprasadviswa:www-data "$WEB_ROOT/.env"
  echo "    Edit: nano $WEB_ROOT/.env"
fi

echo "==> Nginx site $NGINX_SITE"
sudo tee "$NGINX_SITE" > /dev/null <<EOF
server {
    listen 80;
    server_name ${SERVER_NAME};
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

sudo ln -sf "$NGINX_SITE" /etc/nginx/sites-enabled/campustoday-web
sudo nginx -t
sudo systemctl reload nginx

echo ""
echo "Done. Next steps:"
echo "  1. nano $WEB_ROOT/.env   # DB_USER=campus, quote password if it contains #"
echo "  2. sudo certbot --nginx -d ${SERVER_NAME}   # if HTTPS not yet enabled"
echo "  3. Open https://${SERVER_NAME}"
echo "  4. cd $WEB_ROOT && php scripts/smoke_test.php"
