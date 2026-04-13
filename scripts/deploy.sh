#!/usr/bin/env bash
set -Eeuo pipefail

PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$PROJECT_ROOT"

APP_URL_ARG=""
APP_PORT_ARG=""
APP_ENV_ARG="production"
APP_DEBUG_ARG="false"
DB_NAME_ARG=""
DB_USER_ARG=""
DB_PASSWORD_ARG=""
RESET_DB="false"
SKIP_SEED="false"
SKIP_BUILD="false"

log() {
    printf '[deploy] %s\n' "$*"
}

fail() {
    printf '[deploy] ERROR: %s\n' "$*" >&2
    exit 1
}

usage() {
    cat <<'EOF'
Usage:
  ./scripts/deploy.sh [options]

Options:
  --app-url URL         Public application URL. Example: http://server:8080
  --port PORT           Host port for nginx. Default: 8080
  --env NAME            APP_ENV value. Default: production
  --debug true|false    APP_DEBUG value. Default: false
  --db-name NAME        Database name. Default: saas_security
  --db-user NAME        Database user. Default: saas
  --db-password VALUE   Database password. If omitted, a strong random value is generated
  --reset-db            Drop containers and database volume before deploy
  --no-seed             Skip php console.php seed
  --no-build            Skip docker image rebuild
  --help                Show this help

Examples:
  ./scripts/deploy.sh --app-url http://n8nlocal.ru:8080
  ./scripts/deploy.sh --app-url https://example.com --port 8080 --reset-db
EOF
}

validate_bool() {
    local name="$1"
    local value="$2"

    case "$value" in
        true|false)
            ;;
        *)
            fail "$name must be 'true' or 'false', got: $value"
            ;;
    esac
}

validate_port() {
    local value="$1"

    [[ "$value" =~ ^[0-9]+$ ]] || fail "Port must be numeric, got: $value"
    (( value >= 1 && value <= 65535 )) || fail "Port must be between 1 and 65535, got: $value"
}

while [[ $# -gt 0 ]]; do
    case "$1" in
        --app-url)
            APP_URL_ARG="${2:-}"
            shift 2
            ;;
        --port)
            APP_PORT_ARG="${2:-}"
            shift 2
            ;;
        --env)
            APP_ENV_ARG="${2:-}"
            shift 2
            ;;
        --debug)
            APP_DEBUG_ARG="${2:-}"
            shift 2
            ;;
        --db-name)
            DB_NAME_ARG="${2:-}"
            shift 2
            ;;
        --db-user)
            DB_USER_ARG="${2:-}"
            shift 2
            ;;
        --db-password)
            DB_PASSWORD_ARG="${2:-}"
            shift 2
            ;;
        --reset-db)
            RESET_DB="true"
            shift
            ;;
        --no-seed)
            SKIP_SEED="true"
            shift
            ;;
        --no-build)
            SKIP_BUILD="true"
            shift
            ;;
        --help|-h)
            usage
            exit 0
            ;;
        *)
            fail "Unknown option: $1"
            ;;
    esac
done

require_command() {
    command -v "$1" >/dev/null 2>&1 || fail "Required command not found: $1"
}

require_command docker
require_command awk
require_command sed
require_command chmod
require_command curl

if docker compose version >/dev/null 2>&1; then
    COMPOSE_CMD=(docker compose)
elif command -v docker-compose >/dev/null 2>&1; then
    COMPOSE_CMD=(docker-compose)
else
    fail "Docker Compose not found"
fi

compose() {
    "${COMPOSE_CMD[@]}" "$@"
}

get_env_value() {
    local key="$1"
    local file="$2"

    if [[ ! -f "$file" ]]; then
        return 0
    fi

    awk -F= -v key="$key" '$1 == key { sub(/^[^=]*=/, "", $0); gsub(/^"|"$/, "", $0); value=$0 } END { print value }' "$file"
}

set_env_value() {
    local key="$1"
    local value="$2"
    local file="$3"
    local tmp

    tmp="$(mktemp)"
    awk -v key="$key" -v value="$value" '
        BEGIN { done = 0 }
        $0 ~ ("^" key "=") {
            print key "=" value
            done = 1
            next
        }
        { print }
        END {
            if (!done) {
                print key "=" value
            }
        }
    ' "$file" > "$tmp"
    mv "$tmp" "$file"
}

generate_secret() {
    local bytes="${1:-24}"

    if command -v openssl >/dev/null 2>&1; then
        openssl rand -hex "$bytes"
    else
        head -c "$bytes" /dev/urandom | od -An -tx1 | tr -d ' \n'
    fi
}

generate_app_key() {
    generate_secret 16
}

write_nginx_config() {
    cat > "$PROJECT_ROOT/docker/nginx/default.conf" <<'EOF'
server {
    listen 80;
    server_name _;
    root /var/www/html/public;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php$is_args$args;
    }

    location = /index.php {
        include fastcgi_params;
        fastcgi_pass app:9000;
        fastcgi_param SCRIPT_FILENAME /var/www/html/public/index.php;
        fastcgi_param DOCUMENT_ROOT /var/www/html/public;
    }

    location ~ \.php$ {
        return 404;
    }

    location ~* ^/(config|storage|database|cron|docker|app|bootstrap|routes)/ {
        deny all;
    }

    location ~ /\. {
        deny all;
    }
}
EOF
}

normalize_permissions() {
    log "Normalizing filesystem permissions"

    chmod 755 "$PROJECT_ROOT" || true

    for path in app bootstrap config database docker public routes scripts tests cron; do
        if [[ -d "$path" ]]; then
            find "$path" -type d -exec chmod 755 {} \; || true
            find "$path" -type f ! -name '*.sh' -exec chmod 644 {} \; || true
            find "$path" -type f -name '*.sh' -exec chmod 755 {} \; || true
        fi
    done

    [[ -f console.php ]] && chmod 644 console.php || true
    [[ -f .env ]] && chmod 600 .env || true

    if [[ -d storage ]]; then
        find storage -type d -exec chmod 770 {} \; || true
        find storage -type f -exec chmod 640 {} \; || true
    fi
}

wait_for_container_health() {
    local service="$1"
    local timeout_seconds="${2:-180}"
    local container_id
    local status
    local elapsed=0

    container_id="$(compose ps -q "$service")"
    [[ -n "$container_id" ]] || fail "Container id not found for service: $service"

    while (( elapsed < timeout_seconds )); do
        status="$(docker inspect --format '{{if .State.Health}}{{.State.Health.Status}}{{else}}{{.State.Status}}{{end}}' "$container_id" 2>/dev/null || true)"

        if [[ "$status" == "healthy" || "$status" == "running" ]]; then
            log "Service '$service' is $status"
            return 0
        fi

        sleep 2
        elapsed=$((elapsed + 2))
    done

    compose logs "$service" --tail=100 || true
    fail "Service '$service' did not become healthy in time"
}

wait_for_http() {
    local url="$1"
    local timeout_seconds="${2:-120}"
    local elapsed=0

    while (( elapsed < timeout_seconds )); do
        if curl --silent --show-error --fail "$url" >/dev/null 2>&1; then
            log "HTTP check passed: $url"
            return 0
        fi

        sleep 2
        elapsed=$((elapsed + 2))
    done

    fail "HTTP check failed: $url"
}

if [[ ! -f .env ]]; then
    log "Creating .env"
    if [[ -f .env.example ]]; then
        cp .env.example .env
    else
        : > .env
    fi
fi

APP_PORT="${APP_PORT_ARG:-$(get_env_value APP_PORT .env)}"
APP_PORT="${APP_PORT:-8080}"
validate_port "$APP_PORT"

APP_URL="${APP_URL_ARG:-$(get_env_value APP_URL .env)}"
APP_URL="${APP_URL:-http://localhost:${APP_PORT}}"

DB_NAME="${DB_NAME_ARG:-$(get_env_value DB_DATABASE .env)}"
DB_NAME="${DB_NAME:-saas_security}"

DB_USER="${DB_USER_ARG:-$(get_env_value DB_USERNAME .env)}"
DB_USER="${DB_USER:-saas}"

DB_PASSWORD="${DB_PASSWORD_ARG:-$(get_env_value DB_PASSWORD .env)}"
if [[ -z "$DB_PASSWORD" || "$DB_PASSWORD" == "saas_secret" ]]; then
    DB_PASSWORD="$(generate_secret 18)"
fi

DB_ROOT_PASSWORD="$(get_env_value DB_ROOT_PASSWORD .env)"
if [[ -z "$DB_ROOT_PASSWORD" ]]; then
    DB_ROOT_PASSWORD="$DB_PASSWORD"
fi

SEED_ADMIN_EMAIL="$(get_env_value SEED_ADMIN_EMAIL .env)"
SEED_ADMIN_EMAIL="${SEED_ADMIN_EMAIL:-admin@example.com}"

SEED_ADMIN_PASSWORD="$(get_env_value SEED_ADMIN_PASSWORD .env)"
if [[ -z "$SEED_ADMIN_PASSWORD" || "$SEED_ADMIN_PASSWORD" == "ChangeMe123!" ]]; then
    SEED_ADMIN_PASSWORD="$(generate_secret 18)"
fi

validate_bool APP_DEBUG "$APP_DEBUG_ARG"

SESSION_SECURE_COOKIE="false"
if [[ "$APP_URL" == https://* ]]; then
    SESSION_SECURE_COOKIE="true"
fi

APP_KEY="$(get_env_value APP_KEY .env)"
if [[ -z "$APP_KEY" || "$APP_KEY" == "change-me-to-a-random-32-char-string" ]]; then
    APP_KEY="$(generate_app_key)"
fi

log "Updating .env"
set_env_value APP_URL "$APP_URL" .env
set_env_value APP_PORT "$APP_PORT" .env
set_env_value APP_ENV "$APP_ENV_ARG" .env
set_env_value APP_DEBUG "$APP_DEBUG_ARG" .env
set_env_value SESSION_SECURE_COOKIE "$SESSION_SECURE_COOKIE" .env
set_env_value DB_HOST "db" .env
set_env_value DB_PORT "3306" .env
set_env_value DB_DATABASE "$DB_NAME" .env
set_env_value DB_USERNAME "$DB_USER" .env
set_env_value DB_PASSWORD "$DB_PASSWORD" .env
set_env_value DB_ROOT_PASSWORD "$DB_ROOT_PASSWORD" .env
set_env_value APP_KEY "$APP_KEY" .env
set_env_value SEED_ADMIN_EMAIL "$SEED_ADMIN_EMAIL" .env
set_env_value SEED_ADMIN_PASSWORD "$SEED_ADMIN_PASSWORD" .env

write_nginx_config
normalize_permissions

if [[ "$RESET_DB" == "true" ]]; then
    log "Stopping stack and removing database volume"
    compose down -v --remove-orphans || true
else
    log "Stopping current stack"
    compose down --remove-orphans || true
fi

log "Starting containers"
if [[ "$SKIP_BUILD" == "true" ]]; then
    compose up -d
else
    compose up -d --build
fi

wait_for_container_health db 180
wait_for_container_health app 120
wait_for_container_health nginx 120

log "Running migrations"
compose exec -T app php console.php migrate

if [[ "$SKIP_SEED" != "true" ]]; then
    log "Running seeders"
    compose exec -T app php console.php seed
fi

log "Running smoke test"
compose exec -T app php tests/SmokeTest.php

log "Recreating worker after migrations"
compose rm -sf worker >/dev/null 2>&1 || true
compose up -d worker
wait_for_container_health worker 120

wait_for_http "http://127.0.0.1:${APP_PORT}/health" 120
wait_for_http "http://127.0.0.1:${APP_PORT}/ready" 120

log "Current containers:"
compose ps

cat <<EOF

Deployment completed successfully.

App URL:      ${APP_URL}
Health URL:   http://127.0.0.1:${APP_PORT}/health
Ready URL:    http://127.0.0.1:${APP_PORT}/ready

Seed admin after deploy:
  Email:    ${SEED_ADMIN_EMAIL}
  Password: stored in .env as SEED_ADMIN_PASSWORD
EOF

if [[ "$APP_URL" == https://* ]]; then
    cat <<EOF

Note:
  APP_URL points to HTTPS. This is correct only if you already terminate TLS
  on a reverse proxy such as Traefik, Caddy, or Nginx in front of this stack.
EOF
else
    cat <<EOF

Warning:
  APP_URL is using plain HTTP. Public production access should be placed behind HTTPS,
  otherwise session cookies and login traffic are not protected in transit.
EOF
fi
