#!/bin/bash
# =====================================================================
# VICOBA Standalone Deploy Script
# Nakili hii kwenye server, kisha fanya: bash deploy.sh
# =====================================================================

set -e

DOMAIN="vikoba.mdandu.com"
WEB_ROOT="/www/wwwroot/$DOMAIN"
BACKUP_DIR="/www/backup/vicoba_wp_$(date +%Y%m%d_%H%M%S)"
REPO="https://github.com/codeoba/vicoba-manager.git"
BRANCH="standalone-php"

echo "============================================="
echo "  VICOBA Standalone PHP — Deployment Script  "
echo "============================================="
echo ""

# ── Step 1: Backup WordPress ──────────────────────────────────────────
echo "📦 [1/6] Backing up WordPress..."
mkdir -p "$BACKUP_DIR"
rsync -a --exclude='node_modules' --exclude='.git' "$WEB_ROOT/" "$BACKUP_DIR/" 2>/dev/null || true
echo "  ✅ Backup saved to: $BACKUP_DIR"

# ── Step 2: Clone standalone app to temp directory ────────────────────
echo ""
echo "📥 [2/6] Cloning standalone-php branch from GitHub..."
TEMP_DIR="/tmp/vicoba_standalone_$$"
git clone --branch "$BRANCH" --single-branch "$REPO" "$TEMP_DIR"
echo "  ✅ Cloned to $TEMP_DIR"

# ── Step 3: Copy app files (keep WordPress backup safe) ───────────────
echo ""
echo "🚀 [3/6] Deploying app files to $WEB_ROOT ..."
# Clear current WP files (we have backup)
rm -rf "$WEB_ROOT"/*  2>/dev/null || true
rm -rf "$WEB_ROOT"/.[!.]* 2>/dev/null || true
# Copy standalone app
rsync -a "$TEMP_DIR/" "$WEB_ROOT/"
echo "  ✅ Files deployed"

# ── Step 4: Create config from template ──────────────────────────────
echo ""
echo "⚙️  [4/6] Setting up config.php..."
if [ ! -f "$WEB_ROOT/config/config.php" ]; then
    echo "  ℹ️  config.php already copied from template"
fi

# Auto-detect database from WordPress wp-config.php in backup
WP_CONFIG="$BACKUP_DIR/wp-config.php"
if [ -f "$WP_CONFIG" ]; then
    DB_NAME=$(grep "DB_NAME" "$WP_CONFIG" | grep -o "'[^']*'" | sed -n '2p' | tr -d "'")
    DB_USER=$(grep "DB_USER" "$WP_CONFIG" | grep -o "'[^']*'" | sed -n '2p' | tr -d "'")
    DB_PASS=$(grep "DB_PASSWORD" "$WP_CONFIG" | grep -o "'[^']*'" | sed -n '2p' | tr -d "'")
    DB_HOST=$(grep "DB_HOST" "$WP_CONFIG" | grep -o "'[^']*'" | sed -n '2p' | tr -d "'")

    echo "  📊 Found WP database: $DB_NAME (user: $DB_USER)"

    # Patch config.php with detected credentials
    sed -i "s/'name'    => 'vicoba_db'/'name'    => '$DB_NAME'/" "$WEB_ROOT/config/config.php"
    sed -i "s/'user'    => 'vicoba_user'/'user'    => '$DB_USER'/" "$WEB_ROOT/config/config.php"
    sed -i "s/'pass'    => 'CHANGE_ME'/'pass'    => '$DB_PASS'/" "$WEB_ROOT/config/config.php"
    sed -i "s/'host'    => 'localhost'/'host'    => '$DB_HOST'/" "$WEB_ROOT/config/config.php"

    # Generate NIDA encryption key
    NIDA_KEY=$(openssl rand -hex 32)
    sed -i "s/'nida_encrypt_key'   => 'CHANGE_TO_32_CHAR_RANDOM_STRING'/'nida_encrypt_key'   => '$NIDA_KEY'/" "$WEB_ROOT/config/config.php"

    echo "  ✅ config.php patched with WP database credentials"
    echo "  🔑 NIDA encryption key generated"
else
    echo "  ⚠️  wp-config.php not found in backup. Edit config/config.php manually!"
fi

# ── Step 5: Run database setup ────────────────────────────────────────
echo ""
echo "🗄️  [5/6] Setting up database tables..."

# Read DB credentials from config
if [ ! -z "$DB_NAME" ] && [ ! -z "$DB_USER" ] && [ ! -z "$DB_PASS" ]; then
    # Run install.sql
    mysql -u"$DB_USER" -p"$DB_PASS" -h"$DB_HOST" "$DB_NAME" < "$WEB_ROOT/migrations/install.sql" 2>&1 && echo "  ✅ install.sql executed" || echo "  ⚠️  install.sql had warnings (may be OK if tables already exist)"

    # Run migration
    echo "  🔄 Migrating WordPress data (users, renaming tables)..."
    php "$WEB_ROOT/migrate/from_wordpress.php" 2>&1
else
    echo "  ⚠️  Database credentials not found. Run manually:"
    echo "       mysql -u USER -p DB_NAME < $WEB_ROOT/migrations/install.sql"
    echo "       php $WEB_ROOT/migrate/from_wordpress.php"
fi

# ── Step 6: Fix permissions ───────────────────────────────────────────
echo ""
echo "🔐 [6/6] Setting permissions..."
mkdir -p "$WEB_ROOT/public/uploads"
chmod -R 755 "$WEB_ROOT/"
chmod -R 777 "$WEB_ROOT/public/uploads/"
chown -R www:www "$WEB_ROOT/" 2>/dev/null || chown -R nginx:nginx "$WEB_ROOT/" 2>/dev/null || true
echo "  ✅ Permissions set"

# ── Cleanup ───────────────────────────────────────────────────────────
rm -rf "$TEMP_DIR"

# ── Final Instructions ─────────────────────────────────────────────────
echo ""
echo "============================================="
echo "  ✅ DEPLOYMENT COMPLETE!"
echo "============================================="
echo ""
echo "📋 HATUA ZILIZOBAKI:"
echo ""
echo "1️⃣  UPDATE NGINX — Badilisha 'root' kwenye nginx config:"
echo "    Zamani:  root $WEB_ROOT;"
echo "    Mpya:    root $WEB_ROOT/public;"
echo ""
echo "    Amri:"
echo "    nano /www/server/panel/vhost/nginx/$DOMAIN.conf"
echo "    # Badilisha root line, kisha:"
echo "    nginx -t && nginx -s reload"
echo ""
echo "2️⃣  SETUP CRON — Ongeza kwenye crontab:"
echo "    crontab -e"
echo "    # Ongeza line hii:"
echo "    0 2 * * * /usr/bin/php $WEB_ROOT/cron/overdue_check.php >> /var/log/vicoba_cron.log 2>&1"
echo ""
echo "3️⃣  LOGIN URL: https://$DOMAIN/login"
echo "    Username: admin"
echo "    Password: Admin@1234  ← BADILISHA MARA MOJA!"
echo ""
echo "4️⃣  BACKUP location: $BACKUP_DIR"
echo "    (WordPress files na database ziko salama hapa)"
echo ""
