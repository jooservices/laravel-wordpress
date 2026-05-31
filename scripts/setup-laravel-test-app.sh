#!/usr/bin/env bash
set -euo pipefail

LARAVEL_APP_PATH="${LARAVEL_APP_PATH:-/work/laravel-app}"
PACKAGE_PATH="${PACKAGE_PATH:-/package}"
LARAVEL_DB_DATABASE="${LARAVEL_DB_DATABASE:-laravel_wordpress_test}"
DB_HOST="${DB_HOST:-db}"
DB_PORT="${DB_PORT:-3306}"
DB_USERNAME="${DB_USERNAME:-wordpress}"
DB_PASSWORD="${DB_PASSWORD:-wordpress}"
DB_ROOT_PASSWORD="${DB_ROOT_PASSWORD:-wordpress}"

echo "==> Preparing fresh Laravel app at $LARAVEL_APP_PATH"
rm -rf "$LARAVEL_APP_PATH"
composer create-project laravel/laravel:^12.0 "$LARAVEL_APP_PATH" --no-interaction --prefer-dist

cd "$LARAVEL_APP_PATH"

composer config minimum-stability dev
composer config prefer-stable true
composer config repositories.local-package '{"type":"path","url":"'"$PACKAGE_PATH"'","options":{"symlink":false}}'
composer require jooservices/laravel-wordpress:@dev --no-interaction --with-all-dependencies

mysql -h "$DB_HOST" -P "$DB_PORT" -u root -p"$DB_ROOT_PASSWORD" -e "CREATE DATABASE IF NOT EXISTS \`$LARAVEL_DB_DATABASE\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; GRANT ALL PRIVILEGES ON \`$LARAVEL_DB_DATABASE\`.* TO '$DB_USERNAME'@'%'; FLUSH PRIVILEGES;"

php -r "\$env = file_get_contents('.env'); \$env = preg_replace('/^DB_CONNECTION=.*/m', 'DB_CONNECTION=mysql', \$env); \$env = preg_replace('/^#? DB_HOST=.*/m', 'DB_HOST=$DB_HOST', \$env); \$env = preg_replace('/^#? DB_PORT=.*/m', 'DB_PORT=$DB_PORT', \$env); \$env = preg_replace('/^#? DB_DATABASE=.*/m', 'DB_DATABASE=$LARAVEL_DB_DATABASE', \$env); \$env = preg_replace('/^#? DB_USERNAME=.*/m', 'DB_USERNAME=$DB_USERNAME', \$env); \$env = preg_replace('/^#? DB_PASSWORD=.*/m', 'DB_PASSWORD=$DB_PASSWORD', \$env); file_put_contents('.env', \$env);"
php -r "\$xml = file_get_contents('phpunit.xml'); \$values = ['DB_CONNECTION' => 'mysql', 'DB_HOST' => '$DB_HOST', 'DB_PORT' => '$DB_PORT', 'DB_DATABASE' => '$LARAVEL_DB_DATABASE', 'DB_USERNAME' => '$DB_USERNAME', 'DB_PASSWORD' => '$DB_PASSWORD']; foreach (\$values as \$name => \$value) { \$replacement = '<env name=\"'.\$name.'\" value=\"'.htmlspecialchars(\$value, ENT_QUOTES).'\"/>'; if (preg_match('/<env name=\"'.preg_quote(\$name, '/').'\" value=\"[^\"]*\"\\/>/', \$xml)) { \$xml = preg_replace('/<env name=\"'.preg_quote(\$name, '/').'\" value=\"[^\"]*\"\\/>/', \$replacement, \$xml); } else { \$xml = preg_replace('/(\\s*)<\\/php>/', \"    \".\$replacement.\"\\n\$1</php>\", \$xml, 1); } } file_put_contents('phpunit.xml', \$xml);"

php artisan package:discover --ansi
php artisan vendor:publish --tag=laravel-wordpress-config --force --ansi
php artisan vendor:publish --tag=migrations --force --ansi
cp "$LARAVEL_APP_PATH/vendor/jooservices/laravel-wordpress/database/migrations/2026_05_30_000000_create_laravel_wordpress_tables.php" "$LARAVEL_APP_PATH/database/migrations/2026_05_30_000000_create_laravel_wordpress_tables.php"
php artisan migrate:fresh --force --ansi

mkdir -p tests/Feature
cp "$PACKAGE_PATH/tests/docker/FreshLaravelWordPressIntegrationTest.php" tests/Feature/FreshLaravelWordPressIntegrationTest.php
