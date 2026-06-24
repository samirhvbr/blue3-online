#!/bin/bash
# versão 1.0 - 2026-06-24
#
# Deploy idempotente em /srv/www/blue3.online (ONLINE — página de status).
# Modelado no deploy.sh do WCUP/INTRANET. Pressupõe app Laravel (artisan/composer/npm).
#
# EXECUTAR COMO ROOT. O script delega cada operação ao usuário correto:
#   - APP_USER (default: b3sys)    → git, composer, npm (dono dos arquivos,
#     do .git, de vendor/ e node_modules/)
#   - WEB_USER (default: www-data) → php artisan (precisa escrever em
#     storage/ e bootstrap/cache/)
# Permissões são ajustadas pelo próprio script (root faz chown direto).
#
# - Sai cedo se nada novo no branch.
# - Backup MySQL antes de mexer em schema (mysqldump nativo — sem artisan custom).
# - Pula composer/npm quando os respectivos lockfiles não mudaram.
# - Migrations rodam ANTES do npm build — se o frontend falhar, o schema já está certo.
# - Em caso de erro: sai do modo manutenção, tenta rollback e avisa no Telegram.
# - Lock impede dois deploys simultâneos.
#
# Variáveis esperadas no .env:
#   DEPLOY_APP_USER             - dono dos arquivos/git    (default: b3sys)
#   DEPLOY_WEB_USER             - usuário do Apache/PHP    (default: www-data)
#   DEPLOY_TELEGRAM_BOT_TOKEN   - token do bot (BotFather).
#   DEPLOY_TELEGRAM_CHAT_ID     - id do grupo/canal de notificações.
#   DB_DATABASE / DB_USERNAME / DB_PASSWORD / DB_HOST - conexão MySQL padrão Laravel.
#
# Uso (sempre como root):
#   bash /srv/www/blue3.online/deploy.sh
#   DEPLOY_BRANCH=main bash deploy.sh   # forçar outro branch

set -euo pipefail

# ── Root obrigatório ──────────────────────────────────────────────────────────
if [ "$(id -u)" -ne 0 ]; then
    printf '❌ Este script deve ser executado como root.\n' >&2
    printf '   Uso: bash %s\n' "${BASH_SOURCE[0]}" >&2
    exit 1
fi

# ── Config ────────────────────────────────────────────────────────────────────
DIR="/srv/www/blue3.online"
BRANCH="${DEPLOY_BRANCH:-master}"
LOCK="${DIR}/.deploy.lock"
BACKUP_DIR="/var/backups/blue3-online"
BACKUP="${DEPLOY_BACKUP:-0}"   # 1 = faz backup antes de migrate | 0 = pula
DEFAULT_APP_USER="b3sys"       # dono dos arquivos: git, composer, npm
DEFAULT_WEB_USER="www-data"    # usuário do Apache2/PHP-FPM (Debian: nginx também usa www-data)

# ── Helpers ───────────────────────────────────────────────────────────────────
log() { printf '[%(%H:%M:%S)T] %s\n' -1 "$*"; }

# Lê uma chave do .env sem usar source (evita quebra com quoting).
get_env() {
    local key=$1
    [ -f .env ] || return 0
    { grep -E "^${key}=" .env 2>/dev/null || true; } | head -1 | cut -d= -f2- | sed 's/^"//;s/"$//'
}

# Executa como o dono dos arquivos: git, composer, npm.
# -H garante HOME correto (chaves SSH, ~/.gitconfig, cache do composer/npm).
as_app() { sudo -u "$APP_USER" -H "$@"; }

# Executa como o usuário do servidor web: php artisan.
# Precisa escrever em storage/ e bootstrap/cache/.
as_web() { sudo -u "$WEB_USER" -H "$@"; }

# Notificação Telegram (silenciosa se não configurado).
notify() {
    [ -n "${TG_BOT:-}" ] && [ -n "${TG_CHAT:-}" ] || return 0
    curl -fsS -m 5 -X POST "https://api.telegram.org/bot${TG_BOT}/sendMessage" \
        --data-urlencode "chat_id=${TG_CHAT}" \
        --data-urlencode "text=$1" \
        --data-urlencode "parse_mode=HTML" \
        >/dev/null 2>&1 || log "(notify Telegram falhou; ignorando)"
}

cleanup_on_failure() {
    log "❌ Deploy falhou — reativando aplicação..."
    as_web php artisan up >/dev/null 2>&1 || true
    notify "❌ <b>Deploy ONLINE falhou</b> em <code>$(as_app git rev-parse --short HEAD 2>/dev/null || echo '?')</code>"
}

fail() {
    log "❌ $*"
    cleanup_on_failure
    exit 1
}

# ── Lock ──────────────────────────────────────────────────────────────────────
exec 9>"$LOCK"
flock -n 9 || { printf '❌ outro deploy já está rodando (lock: %s)\n' "$LOCK" >&2; exit 1; }

cd "$DIR" || { printf '❌ %s não existe\n' "$DIR" >&2; exit 1; }

# Lê variáveis do .env
APP_USER=$(get_env DEPLOY_APP_USER)
APP_USER="${APP_USER:-$DEFAULT_APP_USER}"
WEB_USER=$(get_env DEPLOY_WEB_USER)
WEB_USER="${WEB_USER:-$DEFAULT_WEB_USER}"
TG_BOT=$(get_env DEPLOY_TELEGRAM_BOT_TOKEN)
TG_CHAT=$(get_env DEPLOY_TELEGRAM_CHAT_ID)
DB_NAME=$(get_env DB_DATABASE)
DB_USER=$(get_env DB_USERNAME)
DB_PASS=$(get_env DB_PASSWORD)
DB_HOST=$(get_env DB_HOST)

# Valida usuários antes de qualquer operação
id "$APP_USER" &>/dev/null || { printf '❌ usuário APP_USER "%s" não existe\n' "$APP_USER" >&2; exit 1; }
id "$WEB_USER" &>/dev/null || { printf '❌ usuário WEB_USER "%s" não existe\n' "$WEB_USER" >&2; exit 1; }

log "=== Deploy ONLINE — dir: $DIR | app: $APP_USER | web: $WEB_USER ==="

# ── 1. Fetch e checagem de mudanças (como APP_USER, dono do .git) ─────────────
log "==> [1/8] Buscando alterações no branch $BRANCH..."
as_app git fetch --quiet origin "$BRANCH" || { log "git fetch falhou"; exit 1; }

LOCAL=$(as_app git rev-parse HEAD)
REMOTE=$(as_app git rev-parse "origin/$BRANCH")

if [ "$LOCAL" = "$REMOTE" ]; then
    log "✓ Nada novo em origin/$BRANCH. Saindo."
    exit 0
fi

if ! as_app git diff --quiet || ! as_app git diff --cached --quiet; then
    log "⚠️  Working tree tem mudanças locais não commitadas — continuando mesmo assim."
fi

log "==> Trazendo $(as_app git rev-parse --short "$LOCAL") → $(as_app git rev-parse --short "$REMOTE")..."
as_app git merge --ff-only "origin/$BRANCH" \
    || { log "❌ fast-forward falhou (divergiu? resolva manual e rode de novo)"; exit 1; }

CHANGED=$(as_app git diff --name-only "$LOCAL" "$REMOTE")

# A partir daqui qualquer falha dispara cleanup.
trap cleanup_on_failure ERR

# ── 2. Modo manutenção ────────────────────────────────────────────────────────
log "==> [2/8] Ativando modo de manutenção..."
as_web php artisan down --refresh=15

# ── 3. Backup do banco (antes de qualquer migrate) ────────────────────────────
log "==> [3/8] Backup do banco MySQL..."
BACKUP_FILE=""
if [ "$BACKUP" = "1" ]; then
    mkdir -p "$BACKUP_DIR"
    BACKUP_FILE="$BACKUP_DIR/online_$(date +%Y%m%d_%H%M%S).sql.gz"

    if [ -n "$DB_NAME" ] && [ -n "$DB_USER" ]; then
        if MYSQL_PWD="$DB_PASS" mysqldump \
                -h "${DB_HOST:-127.0.0.1}" \
                -u "$DB_USER" \
                "$DB_NAME" \
            | gzip > "$BACKUP_FILE"; then
            log "✓ Backup salvo em: $BACKUP_FILE"
            # Mantém apenas os 10 backups mais recentes
            ls -t "$BACKUP_DIR"/online_*.sql.gz 2>/dev/null | tail -n +11 | xargs -r rm --
        else
            fail "mysqldump falhou — abortando antes de tocar no schema"
        fi
    else
        fail "DB_DATABASE ou DB_USERNAME não configurados — não é seguro prosseguir sem backup"
    fi
else
    log "⚠️  Backup desativado (BACKUP=0) — pulando."
fi

# ── 4. Dependências PHP (só se composer.lock mudou) ───────────────────────────
# Roda como APP_USER, dono de vendor/. www-data não é necessário aqui.
log "==> [4/8] Verificando dependências PHP..."
if echo "$CHANGED" | grep -q '^composer\.lock$'; then
    log "    composer.lock mudou — instalando dependências..."
    as_app composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist
else
    log "✓ composer.lock inalterado — pulando composer install."
fi

# ── 5. Migrations ─────────────────────────────────────────────────────────────
# Roda ANTES do npm: se o frontend falhar, o schema já está atualizado e a
# aplicação pode subir de volta sem erro de coluna/tabela faltando.
log "==> [5/8] Migrations..."
PENDING=$(as_web php artisan migrate:status 2>/dev/null | grep -i "pending" || true)
if [ -n "$PENDING" ]; then
    log "    Pendentes:"
    echo "$PENDING"
else
    log "    (nenhuma pendente)"
fi

if ! as_web php artisan migrate --force; then
    log "❌ migrate falhou — tentando rollback do último batch..."
    if as_web php artisan migrate:rollback --force --step=1; then
        log "✓ Rollback OK — schema voltou ao estado anterior."
    else
        log "⚠️  Rollback também falhou. Restaure manualmente de: $BACKUP_FILE"
    fi
    fail "migrate falhou"
fi

# ── 6. Frontend ───────────────────────────────────────────────────────────────
# Roda como APP_USER, dono de node_modules/ e public/build/.
#
# npm ci só quando as dependências mudam (ou node_modules sumiu) — é a parte cara.
# O BUILD roda SEMPRE: o Tailwind reescaneia as fontes (resources/**/*.blade.php e
# *.js) a cada build, então uma classe nova numa VIEW exige rebuild mesmo sem
# mudança em package.json. O build é rápido; rodar sempre evita CSS desatualizado.
log "==> [6/8] Frontend (build sempre — Tailwind reescaneia as fontes)..."
if echo "$CHANGED" | grep -qE '^package(-lock)?\.json' || [ ! -d node_modules ]; then
    log "    Dependências mudaram (ou node_modules ausente) — npm ci..."
    as_app npm ci --no-audit --no-fund --silent
fi
as_app npm run build

# ── 7. Permissões de escrita para o servidor web (root faz chown direto) ──────
# storage/ e bootstrap/cache/ precisam ser graváveis pelo WEB_USER após qualquer
# criação de arquivo pelo composer install ou npm build.
log "==> [7/8] Ajustando permissões..."
chown -R "$APP_USER":"$WEB_USER" storage bootstrap/cache
chmod -R g+w storage bootstrap/cache
log "✓ storage/ e bootstrap/cache/ com escrita para $WEB_USER."

# ── 8. Caches de produção + sai da manutenção ─────────────────────────────────
log "==> [8/8] Reconstruindo caches..."
as_web php artisan optimize:clear
as_web php artisan config:cache
as_web php artisan route:cache
as_web php artisan view:cache
as_web php artisan event:cache

log "==> Desativando modo de manutenção..."
as_web php artisan up

# Sucesso
trap - ERR

SHORT_SHA=$(as_app git rev-parse --short HEAD)
SUBJECT=$(as_app git log -1 --pretty=%s | head -c 80)

log "✅ Deploy ONLINE concluído: $SHORT_SHA — $SUBJECT"
notify "✅ <b>Deploy ONLINE concluído</b>: <code>$SHORT_SHA</code> — $SUBJECT"
