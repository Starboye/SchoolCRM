#!/usr/bin/env bash
# Deploy SchoolCRM web app from GitHub onto the GCP VM (campustoday-api).
# Run on the VM via browser SSH after DNS points app.campustoday.in to this host.
#
# Usage:
#   cd ~/SchoolCRM && bash scripts/deploy_vm_web.sh
#
# Prerequisites: nginx, php-fpm, mariadb, git. API already at /var/www/campustoday-api.

set -euo pipefail

REPO_URL="${REPO_URL:-https://github.com/Starboye/SchoolCRM.git}"
WEB_ROOT="${WEB_ROOT:-/var/www/campustoday-web}"
CLONE_DIR="${CLONE_DIR:-/home/meetprasadviswa/SchoolCRM}"
SERVER_NAME="${SERVER_NAME:-app.campustoday.in}"

echo "==> Sync repo $REPO_URL"
if [[ -d "$CLONE_DIR/.git" ]]; then
  git -C "$CLONE_DIR" fetch origin
  git -C "$CLONE_DIR" reset --hard origin/main
else
  git clone --depth 1 "$REPO_URL" "$CLONE_DIR"
fi

echo "==> Deploy files to $WEB_ROOT"
sudo mkdir -p "$WEB_ROOT"
sudo rsync -a --delete \
  --exclude '.git' \
  --exclude 'node_modules' \
  --exclude '.env' \
  "$CLONE_DIR"/ "$WEB_ROOT"/
sudo chown -R meetprasadviswa:www-data "$WEB_ROOT"

if [[ ! -f "$WEB_ROOT/.env" ]]; then
  echo "==> Creating .env from .env.example"
  sudo cp "$WEB_ROOT/.env.example" "$WEB_ROOT/.env"
  sudo chown meetprasadviswa:www-data "$WEB_ROOT/.env"
fi

# Sync DB creds from Laravel API on same VM (Laravel: DB_USERNAME/DB_PASSWORD → web: DB_USER/DB_PASS)
API_ENV="/var/www/campustoday-api/.env"
WEB_ENV="$WEB_ROOT/.env"
if [[ -f "$API_ENV" && -f "$WEB_ENV" ]]; then
  echo "==> Syncing DB credentials from $API_ENV"
  get_env_val() {
    local file="$1" key="$2"
    local line val
    line=$(grep -E "^${key}=" "$file" | tail -1) || return 0
    val="${line#*=}"
    val="${val%\"}"; val="${val#\"}"; val="${val%\'}"; val="${val#\'}"
    printf '%s' "$val"
  }
  api_db=$(get_env_val "$API_ENV" DB_DATABASE)
  api_user=$(get_env_val "$API_ENV" DB_USERNAME)
  api_pass=$(get_env_val "$API_ENV" DB_PASSWORD)
  if [[ -n "$api_db" && -n "$api_user" ]]; then
    sudo sed -i "s/^DB_HOST=.*/DB_HOST=127.0.0.1/" "$WEB_ENV"
    sudo sed -i "s/^DB_NAME=.*/DB_NAME=${api_db}/" "$WEB_ENV"
    sudo sed -i "s/^DB_USER=.*/DB_USER=${api_user}/" "$WEB_ENV"
    sudo sed -i '/^DB_PASSWORD=/d' "$WEB_ENV"
    sudo sed -i '/^DB_PASS=/d' "$WEB_ENV"
    if [[ -n "$api_pass" ]]; then
      printf 'DB_PASS="%s"\n' "$api_pass" | sudo tee -a "$WEB_ENV" > /dev/null
    else
      echo 'DB_PASS=' | sudo tee -a "$WEB_ENV" > /dev/null
    fi
    sudo chown meetprasadviswa:www-data "$WEB_ENV"
    echo "    DB synced (DB_USER/DB_PASS). Verify: grep ^DB_ $WEB_ENV"
  fi
fi

if [[ -f "$WEB_ENV" ]]; then
  set_brand_env() {
    local key="$1" val="$2"
    if grep -q "^${key}=" "$WEB_ENV"; then
      sudo sed -i "s|^${key}=.*|${key}=${val}|" "$WEB_ENV"
    else
      echo "${key}=${val}" | sudo tee -a "$WEB_ENV" > /dev/null
    fi
  }
  set_brand_env BRAND_LOGO_WIDE_PATH "assets/img/campustoday/header-primary.png"
  sudo chown meetprasadviswa:www-data "$WEB_ENV"
fi

echo "==> Nginx + HTTPS (api vs app hostnames)"
bash "$CLONE_DIR/scripts/fix_vm_nginx.sh"

echo ""
echo "Done. Next steps:"
echo "  1. grep ^DB_ $WEB_ROOT/.env"
echo "  2. Open https://${SERVER_NAME}"
echo "  3. cd $WEB_ROOT && php scripts/smoke_test.php"
