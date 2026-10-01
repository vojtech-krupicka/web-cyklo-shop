# Shared helpers for bin/db-* scripts. Source this file, don't execute it.
# Connection settings come from .env (passed in via docker-compose.yml `env_file`).

DB_HOST="${DB_HOST:-db}"
DB_PORT="${DB_PORT:-3306}"
DB_NAME="${MYSQL_DATABASE:?MYSQL_DATABASE is not set (missing .env?)}"
DB_USER="${MYSQL_USER:?MYSQL_USER is not set (missing .env?)}"

# Password via env so it doesn't show up in `ps` or as a CLI warning
export MYSQL_PWD="${MYSQL_PASSWORD:?MYSQL_PASSWORD is not set (missing .env?)}"

# Connection flags shared by mysql / mysqldump.
# --skip-ssl: traffic stays on the internal Docker network in dev, and it
# silences the MariaDB client's self-signed certificate warning.
DB_ARGS=(--protocol=TCP --skip-ssl -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER")

mysql_cli() { mysql "${DB_ARGS[@]}" "$DB_NAME" "$@"; }
