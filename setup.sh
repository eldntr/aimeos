#!/bin/bash

# Pastikan script dijalankan di dalam direktori project
# Exit if any command fails
set -e

echo "🚀 Memulai proses setup project Laravel Sail..."

# 0. Periksa dan install Docker jika belum ada
if ! command -v docker &> /dev/null
then
    echo "🐳 Docker tidak ditemukan. Menginstall Docker..."
    curl -fsSL https://get.docker.com -o get-docker.sh
    sh get-docker.sh
    rm get-docker.sh
    echo "✅ Docker berhasil diinstall."
else
    echo "✅ Docker sudah terinstall."
fi

# 1. Copy file .env jika belum ada
if [ ! -f .env ]; then
    echo "📄 Menyalin .env.example ke .env..."
    cp .env.example .env
else
    echo "✅ File .env sudah ada."
fi

# 2. Update docker-compose.yml ke PHP 8.4 (karena dependencies membutuhkan PHP 8.4)
echo "🔧 Menyesuaikan versi PHP di docker-compose.yml ke 8.4..."
sed -i 's/sail\/runtimes\/8\.3/sail\/runtimes\/8.4/g' docker-compose.yml
sed -i 's/sail-8\.3/sail-8.4/g' docker-compose.yml
# Hapus obsolete version attribute
sed -i '/^version:/d' docker-compose.yml

# 3. Install Composer dependencies menggunakan Docker (karena vendor belum ada)
# Menggunakan image laravelsail/php84-composer agar sesuai dengan kebutuhan package (PHP >= 8.4.1)
echo "📦 Menginstall dependency Composer..."
docker run --rm \
    --network host \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php84-composer:latest \
    composer install --ignore-platform-req='ext-*' --ignore-platform-req='lib-*'

# 4. Menjalankan container di background (Detached mode)
echo "🐳 Menjalankan container Laravel Sail..."
./vendor/bin/sail up -d

# 5. Generate Application Key (Hanya jika belum ada di .env)
echo "🔑 Memastikan Application Key dan Permission File..."
chmod -R 777 storage bootstrap/cache
chmod 666 .env
./vendor/bin/sail artisan key:generate

# 6. Menjalankan Migrasi Database
echo "🗄️ Menjalankan migrasi database dan setup Aimeos..."
./vendor/bin/sail artisan migrate --force
./vendor/bin/sail artisan aimeos:setup --option=setup/default/demo:0

# 7. Install NPM dependencies dan build assets (Opsional, hapus jika tidak pakai Node/Vite)
echo "🎨 Menginstall NPM dependencies dan build assets..."
mkdir -p node_modules
touch package-lock.json
chmod 777 node_modules
chmod 666 package-lock.json
rm -rf public/build
mkdir -p public/build
chmod 777 public/build
./vendor/bin/sail npm install
./vendor/bin/sail npm run build

echo "✨ Setup selesai! Aplikasi sudah berjalan."
