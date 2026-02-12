#!/usr/bin/env bash
set -euo pipefail

# Feed a .sql dump directly into the mysql CLI client.
# Usage:
#   MYSQL_USER=root MYSQL_PASSWORD='' MYSQL_HOST=127.0.0.1 MYSQL_PORT=3306 \
#   ./scripts/feed_sql_to_mysql.sh asimos_02-02-2026.sql asimos

SQL_FILE="${1:-asimos_02-02-2026.sql}"
DB_NAME="${2:-asimos}"

MYSQL_HOST="${MYSQL_HOST:-127.0.0.1}"
MYSQL_PORT="${MYSQL_PORT:-3306}"
MYSQL_USER="${MYSQL_USER:-root}"
MYSQL_PASSWORD="${MYSQL_PASSWORD:-}"

if [[ ! -f "$SQL_FILE" ]]; then
  echo "Error: SQL file not found: $SQL_FILE" >&2
  exit 1
fi

if ! command -v mysql >/dev/null 2>&1; then
  echo "Error: mysql client is not installed or not in PATH" >&2
  exit 1
fi

# Use MYSQL_PWD to avoid exposing password in process args.
MYSQL_PWD="$MYSQL_PASSWORD" \
mysql \
  --host="$MYSQL_HOST" \
  --port="$MYSQL_PORT" \
  --user="$MYSQL_USER" \
  "$DB_NAME" < "$SQL_FILE"

echo "Imported '$SQL_FILE' into database '$DB_NAME' using mysql CLI."
