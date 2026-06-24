#!/bin/bash
# versão 1.0 - 2026-06-24
#
# setup.sh — provisionamento INICIAL (idempotente) do ONLINE.
# Rode UMA VEZ por servidor, logo após o `git clone` em $DIR. Depois, as
# atualizações passam a ser pelo deploy.sh.
#
# POR QUE EXISTE: o deploy.sh é só UPDATE — num clone já na HEAD do origin ele
# detecta "Nada novo. Saindo." e sai ANTES de composer/npm. A primeira subida
# precisa instalar vendor/, gerar o build do Vite (public/build, que é
# gitignored) e criar o .env com APP_KEY. Sem isso, o Laravel responde HTTP 500
# (página em branco quando APP_DEBUG=false).
#
# FAZ (cada passo com o usuário correto):
#   - .env a partir do .env.example (se ainda não existir)
#   - composer install --no-dev
#   - php artisan key:generate (se APP_KEY vazia)
#   - npm ci + npm run build  → gera public/build/manifest.json
#   - storage:link + permissões de storage/ e bootstrap/cache/
#   - limpa caches (NÃO cacheia config: você ainda vai editar o .env)
# NÃO roda migrate (a página de status usa dados mock; sem schema por ora).
#
# Uso (como root):
#   bash /srv/www/online.blue3/setup.sh

set -euo pipefail

# ── Root obrigatório ──────────────────────────────────────────────────────────
if [ "$(id -u)" -ne 0 ]; then
    printf '❌ rode como root: bash %s\n' "${BASH_SOURCE[0]}" >&2
    exit 1
fi

# ── Config ────────────────────────────────────────────────────────────────────
DIR="/srv/www/online.blue3"
DEFAULT_APP_USER="b3sys"
DEFAULT_WEB_USER="www-data"

# ── Helpers ───────────────────────────────────────────────────────────────────
log() { printf '[%(%H:%M:%S)T] %s\n' -1 "$*"; }

get_env() {
    local key=$1
    [ -f .env ] || return 0
    { grep -E "^${key}=" .env 2>/dev/null || true; } | head -1 | cut -d= -f2- | sed 's/^"//;s/"$//'
}

as_app() { sudo -u "$APP_USER" -H "$@"; }
as_web() { sudo -u "$WEB_USER" -H "$@"; }

cd "$DIR" || { printf '❌ %s não existe — faça o git clone primeiro.\n' "$DIR" >&2; exit 1; }

# Usuários (do .env se já existir; senão, defaults)
APP_USER=$(get_env DEPLOY_APP_USER); APP_USER="${APP_USER:-$DEFAULT_APP_USER}"
WEB_USER=$(get_env DEPLOY_WEB_USER); WEB_USER="${WEB_USER:-$DEFAULT_WEB_USER}"
id "$APP_USER" &>/dev/null || { printf '❌ APP_USER "%s" não existe\n' "$APP_USER" >&2; exit 1; }
id "$WEB_USER" &>/dev/null || { printf '❌ WEB_USER "%s" não existe\n' "$WEB_USER" >&2; exit 1; }

log "=== Setup ONLINE — dir: $DIR | app: $APP_USER | web: $WEB_USER ==="

# ── 1. .env ───────────────────────────────────────────────────────────────────
if [ ! -f .env ]; then
    log "==> Criando .env a partir do .env.example..."
    as_app cp .env.example .env
    log "⚠️  Ajuste o .env depois: APP_ENV=production, APP_DEBUG=false,"
    log "    APP_URL=https://blue3.online, DB_* e tokens do Telegram."
else
    log "✓ .env já existe — preservando."
fi

# ── 2. Dependências PHP ───────────────────────────────────────────────────────
log "==> composer install (--no-dev)..."
as_app composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# ── 3. APP_KEY ────────────────────────────────────────────────────────────────
if [ -z "$(get_env APP_KEY)" ]; then
    log "==> Gerando APP_KEY..."
    as_app php artisan key:generate --force
else
    log "✓ APP_KEY já definida."
fi

# ── 4. Frontend (gera public/build/manifest.json) ─────────────────────────────
log "==> npm ci + build..."
as_app npm ci --no-audit --no-fund --silent
as_app npm run build

# ── 5. Permissões + storage link ──────────────────────────────────────────────
# Permissões PRIMEIRO: storage:link (e qualquer artisan) escreve em public/ e
# pode logar em storage/. Só depois o symlink, como APP_USER (dono de public/).
log "==> Permissões de storage/ e bootstrap/cache/ + storage:link..."
chown -R "$APP_USER":"$WEB_USER" storage bootstrap/cache
chmod -R g+w storage bootstrap/cache
as_app php artisan storage:link 2>/dev/null || true

# ── 6. Limpa caches (sem cachear config — o .env ainda será editado) ──────────
log "==> Limpando caches..."
as_web php artisan optimize:clear

log "✅ Setup concluído."
log "   → Aponte o docroot do webserver para: $DIR/public"
log "   → Confira o .env (APP_ENV=production, APP_DEBUG=false, APP_URL=https://blue3.online)"
log "   → Depois, atualizações são via deploy.sh."
