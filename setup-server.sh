#!/bin/bash
set -e

# Pastikan script dijalankan sebagai root
if [ "$EUID" -ne 0 ]; then
  echo "Harap jalankan script ini sebagai root (gunakan sudo)"
  exit 1
fi

PROJECT_DIR=$(pwd)
ENV_FILE="$PROJECT_DIR/.env"

# Cek apakah file .env ada
if [ ! -f "$ENV_FILE" ]; then
    echo "File .env tidak ditemukan di $PROJECT_DIR!"
    exit 1
fi

# 1. Dapatkan APP_URL dari .env
APP_URL=$(grep -E '^APP_URL=' "$ENV_FILE" | cut -d '=' -f2 | tr -d '"' | tr -d "'")

if [ -z "$APP_URL" ]; then
    echo "APP_URL tidak ditemukan di .env!"
    exit 1
fi

# Bersihkan http:// atau https:// dari APP_URL untuk mendapatkan raw domain
DOMAIN=$(echo "$APP_URL" | sed -e 's|^[^/]*//||' -e 's|/.*$||')

echo "Memulai instalasi dan konfigurasi server untuk domain: $DOMAIN"
echo "Root direktori project: $PROJECT_DIR"

# 2. Install Dependencies (Nginx, Certbot)
echo "Menginstall Nginx dan Certbot..."
apt-get update
apt-get install -y nginx certbot python3-certbot-nginx

# 3. Install Docker jika belum ada
if ! command -v docker &> /dev/null; then
    echo "Menginstall Docker..."
    curl -fsSL https://get.docker.com -o get-docker.sh
    sh get-docker.sh
    rm get-docker.sh
fi

if ! command -v docker compose &> /dev/null && ! docker compose version &> /dev/null; then
    echo "Menginstall Docker Compose plugin..."
    apt-get install -y docker-compose-plugin
fi

# 4. Setup Nginx Configuration
echo "Menyiapkan konfigurasi Nginx..."
TEMPLATE_FILE="$PROJECT_DIR/docker/nginx/template.conf"
CDN_TEMPLATE_FILE="$PROJECT_DIR/docker/nginx/cdn-template.conf"
NGINX_CONF="/etc/nginx/sites-available/$DOMAIN"
CDN_NGINX_CONF="/etc/nginx/sites-available/cdn.$DOMAIN"

if [ ! -f "$TEMPLATE_FILE" ]; then
    echo "File template nginx tidak ditemukan di $TEMPLATE_FILE!"
    exit 1
fi

# Copy template utama dan replace placeholder
cp "$TEMPLATE_FILE" "$NGINX_CONF"
sed -i "s|{{DOMAIN}}|$DOMAIN|g" "$NGINX_CONF"
sed -i "s|{{ROOT_DIR}}|$PROJECT_DIR|g" "$NGINX_CONF"

# Copy template CDN dan replace placeholder
if [ -f "$CDN_TEMPLATE_FILE" ]; then
    echo "Menyiapkan konfigurasi Nginx untuk cdn.$DOMAIN..."
    cp "$CDN_TEMPLATE_FILE" "$CDN_NGINX_CONF"
    sed -i "s|{{DOMAIN}}|$DOMAIN|g" "$CDN_NGINX_CONF"
    ln -sf "$CDN_NGINX_CONF" /etc/nginx/sites-enabled/
fi

# Enable konfigurasi utama
ln -sf "$NGINX_CONF" /etc/nginx/sites-enabled/

# Hapus default nginx config jika ada agar tidak bentrok
if [ -f "/etc/nginx/sites-enabled/default" ]; then
    rm /etc/nginx/sites-enabled/default
fi

# Test dan reload Nginx
nginx -t
systemctl reload nginx

# 5. Menjalankan Docker Compose
echo "Menjalankan aplikasi dengan Docker Compose..."
# Menjalankan services
docker compose up -d --build

# 6. Menjalankan Certbot untuk SSL
echo "Meminta sertifikat SSL dari Let's Encrypt untuk $DOMAIN, www.$DOMAIN, dan cdn.$DOMAIN..."
certbot --nginx -d "$DOMAIN" -d "www.$DOMAIN" -d "cdn.$DOMAIN" --non-interactive --agree-tos -m "admin@$DOMAIN" || echo "Peringatan: SSL gagal dipasang. Pastikan DNS sudah pointing ke server ini!"

echo "======================================"
echo "Instalasi Selesai!"
echo "Server berhasil disiapkan di: https://$DOMAIN"
echo "======================================"
