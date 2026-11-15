#!/usr/bin/env bash
# Installs Hisabi on an Ubuntu 22.04+ or Debian 12+ server, and upgrades it when run again.
#
#   curl -fsSL https://raw.githubusercontent.com/hisabi-app/hisabi/main/install.sh -o install.sh
#   sudo bash install.sh --domain hisabi.example.com
#
# It sets up PHP 8.4, MySQL (MariaDB on Debian), Node to build the frontend, and Caddy, which
# gets an HTTPS certificate for the domain by itself. The app lives in /var/www/hisabi and runs
# as its own user, hisabi. Running the script again pulls the latest code, rebuilds and migrates;
# the .env, the database and its password are kept.
#
# Options:
#   --domain NAME   The domain pointing at this server (DNS first, ports 80 and 443 open).
#                   Without one, Hisabi is served over plain http on port 80.
#   --import FILE   A mysqldump of an existing Hisabi to start from (first install only).
#
# Environment: HISABI_DIR (/var/www/hisabi), HISABI_REPO, HISABI_BRANCH (main).
set -euo pipefail

# The script is one block that bash reads whole before running it: an upgrade pulls a new copy
# of this file, and bash would otherwise go on reading the new one halfway through.
{
APP_DIR="${HISABI_DIR:-/var/www/hisabi}"
REPO="${HISABI_REPO:-https://github.com/hisabi-app/hisabi.git}"
BRANCH="${HISABI_BRANCH:-main}"
APP_USER=hisabi
APP_HOME=/var/lib/hisabi
DB_NAME=hisabi
DB_USER=hisabi
PHP_VERSION=8.4
PHP="php$PHP_VERSION"
FPM_SOCKET=/run/php/hisabi.sock
CADDY_SITE=/etc/caddy/hisabi.caddy
NODE_MAJOR=22

DOMAIN=""
IMPORT=""

say() { printf '\n\033[1m==> %s\033[0m\n' "$*"; }
fail() { printf '\n\033[31mError:\033[0m %s\n' "$*" >&2; exit 1; }
# Prints the comment at the top of this file
usage() { awk 'NR > 1 && /^#/ { sub(/^# ?/, ""); print; next } NR > 1 { exit }' "$0"; exit "${1:-0}"; }

while [ $# -gt 0 ]; do
    case "$1" in
        --domain) DOMAIN="${2:-}"; shift 2 ;;
        --domain=*) DOMAIN="${1#*=}"; shift ;;
        --import) IMPORT="${2:-}"; shift 2 ;;
        --import=*) IMPORT="${1#*=}"; shift ;;
        -h|--help) usage ;;
        *) printf 'Unknown option: %s\n\n' "$1" >&2; usage 1 ;;
    esac
done

[ "$(id -u)" -eq 0 ] || fail "Run it as root: sudo bash $0"
[ -r /etc/os-release ] || fail "This script is for Ubuntu or Debian servers."
# shellcheck source=/dev/null
. /etc/os-release
case "$ID" in
    ubuntu) [ "${VERSION_ID%%.*}" -ge 22 ] || fail "Ubuntu 22.04 or newer is needed (this is $VERSION_ID)." ;;
    debian) [ "${VERSION_ID%%.*}" -ge 12 ] || fail "Debian 12 or newer is needed (this is ${VERSION_ID:-unknown})." ;;
    *) fail "This script is for Ubuntu or Debian servers (this is $ID)." ;;
esac

if [ -n "$IMPORT" ]; then
    [ -r "$IMPORT" ] || fail "Can't read $IMPORT."
    IMPORT="$(cd "$(dirname "$IMPORT")" && pwd)/$(basename "$IMPORT")"
fi

# Commands for the app run as its own user, from the app folder
as_app() { (cd "$APP_DIR" && runuser -u "$APP_USER" -- env HOME="$APP_HOME" "$@"); }
artisan() { as_app "$PHP" artisan "$@"; }
mysql_root() { mysql --protocol=socket -uroot "$@"; }

# Sets KEY=VALUE in the .env, adding the line if it isn't there
set_env() {
    if grep -q "^$1=" "$APP_DIR/.env"; then
        sed -i "s|^$1=.*|$1=$2|" "$APP_DIR/.env"
    else
        printf '%s=%s\n' "$1" "$2" >> "$APP_DIR/.env"
    fi
}

has_tty() { [ -t 0 ] || { [ -r /dev/tty ] && (: < /dev/tty) 2>/dev/null; }; }

# The domain: from --domain, else the one this server already uses, else asked on the first install
if [ -z "$DOMAIN" ] && [ -f "$APP_DIR/.env" ]; then
    DOMAIN="$(sed -n 's|^APP_URL=https://\([^/]*\).*|\1|p' "$APP_DIR/.env")"
elif [ -z "$DOMAIN" ] && has_tty; then
    printf 'The domain for Hisabi (its DNS must point at this server), or Enter for plain http: '
    read -r DOMAIN < /dev/tty || true
fi
DOMAIN="${DOMAIN#http*://}"
DOMAIN="${DOMAIN%%/*}"

say "Installing packages"
export DEBIAN_FRONTEND=noninteractive
apt-get update -q
apt-get install -y -q ca-certificates curl gnupg git unzip

install -d -m 0755 /etc/apt/keyrings

if ! apt-cache show "$PHP-fpm" > /dev/null 2>&1; then
    if [ "$ID" = ubuntu ]; then
        apt-get install -y -q software-properties-common
        add-apt-repository -y ppa:ondrej/php
    else
        curl -fsSLo /tmp/debsuryorg-archive-keyring.deb https://packages.sury.org/debsuryorg-archive-keyring.deb
        dpkg -i /tmp/debsuryorg-archive-keyring.deb
        rm -f /tmp/debsuryorg-archive-keyring.deb
        echo "deb [signed-by=/usr/share/keyrings/debsuryorg-archive-keyring.gpg] https://packages.sury.org/php/ $VERSION_CODENAME main" \
            > /etc/apt/sources.list.d/php.list
    fi
fi

node_major="$(node --version 2>/dev/null | sed 's/^v\([0-9]*\).*/\1/' || true)"
if [ "${node_major:-0}" -lt "$NODE_MAJOR" ]; then
    curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key | gpg --dearmor --yes -o /etc/apt/keyrings/nodesource.gpg
    echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_$NODE_MAJOR.x nodistro main" \
        > /etc/apt/sources.list.d/nodesource.list
fi

if ! command -v caddy > /dev/null; then
    curl -fsSL https://dl.cloudsmith.io/public/caddy/stable/gpg.key | gpg --dearmor --yes -o /usr/share/keyrings/caddy-stable-archive-keyring.gpg
    curl -fsSL https://dl.cloudsmith.io/public/caddy/stable/debian.deb.txt > /etc/apt/sources.list.d/caddy-stable.list
fi

# Debian ships MariaDB in place of MySQL
mysql_package=mysql-server
[ "$ID" = debian ] && mysql_package=default-mysql-server

apt-get update -q
apt-get install -y -q "$PHP-fpm" "$PHP-cli" "$PHP-mysql" "$PHP-mbstring" "$PHP-xml" "$PHP-curl" "$PHP-zip" "$PHP-intl" \
    nodejs caddy "$mysql_package"
systemctl enable -q --now mysql 2> /dev/null || systemctl enable -q --now mariadb

if ! command -v composer > /dev/null; then
    say "Installing Composer"
    expected="$(curl -fsSL https://composer.github.io/installer.sig)"
    curl -fsSLo /tmp/composer-setup.php https://getcomposer.org/installer
    [ "$("$PHP" -r "echo hash_file('sha384', '/tmp/composer-setup.php');")" = "$expected" ] || fail "The Composer installer's checksum didn't match."
    "$PHP" /tmp/composer-setup.php --quiet --install-dir=/usr/local/bin --filename=composer
    rm -f /tmp/composer-setup.php
fi

id "$APP_USER" > /dev/null 2>&1 || useradd --system --create-home --home-dir "$APP_HOME" --shell /usr/sbin/nologin "$APP_USER"

if [ -d "$APP_DIR/.git" ]; then
    # Down while the code, packages and database change, back up when the script ends
    if grep -qs '^APP_KEY=base64:' "$APP_DIR/.env"; then
        artisan down --retry=15 || true
        trap 'artisan up > /dev/null || true' EXIT
    fi

    say "Updating the code"
    as_app git pull --ff-only
else
    say "Downloading Hisabi"
    [ ! -e "$APP_DIR" ] || [ -z "$(ls -A "$APP_DIR")" ] || fail "$APP_DIR exists and isn't a Hisabi checkout."
    mkdir -p "$(dirname "$APP_DIR")"
    install -d -o "$APP_USER" -g "$APP_USER" -m 0755 "$APP_DIR"
    as_app git clone --branch "$BRANCH" "$REPO" "$APP_DIR"
fi

if [ ! -f "$APP_DIR/.env" ]; then
    say "Creating the database"
    db_password="$("$PHP" -r 'echo bin2hex(random_bytes(24));')"
    mysql_root <<SQL
CREATE DATABASE IF NOT EXISTS \`$DB_NAME\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$db_password';
ALTER USER '$DB_USER'@'localhost' IDENTIFIED BY '$db_password';
GRANT ALL PRIVILEGES ON \`$DB_NAME\`.* TO '$DB_USER'@'localhost';
FLUSH PRIVILEGES;
SQL

    # The database is reached through its socket, so it never needs a network port
    install -o "$APP_USER" -g "$APP_USER" -m 0600 /dev/null "$APP_DIR/.env"
    cat > "$APP_DIR/.env" <<ENV
APP_NAME=Hisabi
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=

LOG_CHANNEL=daily
LOG_LEVEL=warning

DB_CONNECTION=mysql
DB_HOST=localhost
DB_SOCKET=/run/mysqld/mysqld.sock
DB_DATABASE=$DB_NAME
DB_USERNAME=$DB_USER
DB_PASSWORD=$db_password

CACHE_DRIVER=file
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120
ENV
fi

if [ -n "$DOMAIN" ]; then
    set_env APP_URL "https://$DOMAIN"
    set_env SESSION_SECURE_COOKIE true
else
    set_env APP_URL "http://$(hostname -I 2>/dev/null | awk '{print $1}')"
    set_env SESSION_SECURE_COOKIE false
fi

# The code is readable (Caddy serves public/), but secrets, logs and sessions are the app's alone
chmod 0600 "$APP_DIR/.env"
chmod 0700 "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"

say "Building"
as_app "$PHP" /usr/local/bin/composer install --no-dev --optimize-autoloader --no-interaction --no-progress
as_app npm ci --no-audit --no-fund
as_app npm run build
rm -rf "$APP_DIR/node_modules"

grep -q '^APP_KEY=base64:' "$APP_DIR/.env" || artisan key:generate --force

if [ -n "$IMPORT" ]; then
    tables="$(mysql_root -N -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = '$DB_NAME'")"
    [ "$tables" -eq 0 ] || fail "The database already has tables, so --import is only for a first install."
    say "Importing $IMPORT"
    mysql_root "$DB_NAME" < "$IMPORT"
fi

say "Migrating the database"
artisan migrate --force
# No config:cache, so changes to .env and config/hisabi.php (SMS templates) apply right away
artisan config:clear
artisan route:cache
artisan view:cache

say "Setting up PHP and Caddy"
cat > "/etc/php/$PHP_VERSION/fpm/pool.d/hisabi.conf" <<POOL
; Hisabi (written by install.sh)
[hisabi]
user = $APP_USER
group = $APP_USER
listen = $FPM_SOCKET
listen.owner = caddy
listen.group = caddy
listen.mode = 0660
pm = ondemand
pm.max_children = 8
pm.process_idle_timeout = 30s
POOL
systemctl enable -q "$PHP-fpm"
systemctl restart "$PHP-fpm"

if [ -n "$DOMAIN" ]; then
    site="$DOMAIN"
    hsts='Strict-Transport-Security "max-age=31536000"'
else
    site=":80"
    hsts=""
fi

cat > "$CADDY_SITE" <<CADDY
# Hisabi (written by install.sh; running it again rewrites this file)
$site {
	root * $APP_DIR/public
	encode zstd gzip
	php_fastcgi unix/$FPM_SOCKET
	file_server

	header {
		X-Content-Type-Options nosniff
		X-Frame-Options DENY
		Referrer-Policy same-origin
		$hsts
		-X-Powered-By
	}
}
CADDY

# The stock Caddyfile (a placeholder site) is replaced; one you wrote yourself is left alone
caddyfile=/etc/caddy/Caddyfile
if [ ! -f "$caddyfile" ] || grep -q 'root \* /usr/share/caddy' "$caddyfile" || grep -q 'written by install.sh' "$caddyfile"; then
    printf '# Hisabi (written by install.sh)\nimport %s\n' "$CADDY_SITE" > "$caddyfile"
elif ! grep -q "import $CADDY_SITE" "$caddyfile"; then
    printf '\nYour %s is your own, so it was left alone. Add this line to it, then run: systemctl reload caddy\n  import %s\n' "$caddyfile" "$CADDY_SITE"
fi

caddy validate --config "$caddyfile" --adapter caddyfile > /dev/null 2>&1 || fail "Caddy doesn't accept $caddyfile (check it with: caddy validate --config $caddyfile)"
systemctl enable -q caddy
systemctl reload caddy || systemctl restart caddy

if command -v ufw > /dev/null && ufw status | grep -q 'Status: active'; then
    ufw allow 80/tcp > /dev/null
    ufw allow 443/tcp > /dev/null
fi

# How to run the app's commands yourself (minimal Debian has no sudo)
run_as="sudo -u $APP_USER"
command -v sudo > /dev/null || run_as="runuser -u $APP_USER --"

users="$(mysql_root -N -e "SELECT COUNT(*) FROM \`$DB_NAME\`.users")"
if [ "$users" -eq 0 ]; then
    if has_tty; then
        say "Creating your account"
        artisan hisabi:install < /dev/tty
    else
        printf '\nCreate your account with:\n  cd %s && %s %s artisan hisabi:install\n' "$APP_DIR" "$run_as" "$PHP"
    fi
fi

url="$(sed -n 's/^APP_URL=//p' "$APP_DIR/.env")"
say "Hisabi is running at $url"
if [ -z "$DOMAIN" ]; then
    printf 'It is served over plain http, so your password and session travel unencrypted.\n'
    printf 'Point a domain at this server and run: sudo bash %s --domain your.domain\n' "$0"
fi
printf 'To upgrade later, run this script again.\n'
printf 'For SMS from your Mac (tools/mac-sms-sync), make a token with:\n  cd %s && %s %s artisan hisabi:sms-sync-token mac\n' "$APP_DIR" "$run_as" "$PHP"
exit
}
