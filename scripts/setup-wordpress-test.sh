#!/usr/bin/env bash
set -euo pipefail

WORDPRESS_PATH="${WORDPRESS_PATH:-/work/wordpress}"
WORDPRESS_URL="${WORDPRESS_URL:-http://app:8080}"
WORDPRESS_DB_NAME="${WORDPRESS_DB_NAME:-wordpress}"
WORDPRESS_DB_USER="${WORDPRESS_DB_USER:-wordpress}"
WORDPRESS_DB_PASSWORD="${WORDPRESS_DB_PASSWORD:-wordpress}"
WORDPRESS_DB_HOST="${WORDPRESS_DB_HOST:-db:3306}"
WORDPRESS_ADMIN_USER="${WORDPRESS_ADMIN_USER:-admin}"
WORDPRESS_ADMIN_PASSWORD="${WORDPRESS_ADMIN_PASSWORD:-password}"
WORDPRESS_ADMIN_EMAIL="${WORDPRESS_ADMIN_EMAIL:-admin@example.com}"
DB_HOST="${DB_HOST:-db}"
DB_PORT="${DB_PORT:-3306}"
DB_USERNAME="${DB_USERNAME:-wordpress}"
DB_PASSWORD="${DB_PASSWORD:-wordpress}"
DB_ROOT_PASSWORD="${DB_ROOT_PASSWORD:-wordpress}"
export WP_CLI_PHP_ARGS="${WP_CLI_PHP_ARGS:--d memory_limit=-1}"

numeric_id() {
  awk '/^[0-9]+$/ { id = $1 } END { if (id != "") print id }'
}

require_id() {
  local name="$1"
  local value="$2"

  if [[ -z "$value" ]]; then
    echo "Unable to resolve numeric WordPress ID for $name" >&2
    exit 1
  fi
}

echo "==> Installing WordPress at $WORDPRESS_PATH"
rm -rf "$WORDPRESS_PATH"
mkdir -p "$WORDPRESS_PATH"

mysql -h "$DB_HOST" -P "$DB_PORT" -u root -p"$DB_ROOT_PASSWORD" -e "DROP DATABASE IF EXISTS \`$WORDPRESS_DB_NAME\`; CREATE DATABASE \`$WORDPRESS_DB_NAME\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; GRANT ALL PRIVILEGES ON \`$WORDPRESS_DB_NAME\`.* TO '$DB_USERNAME'@'%'; FLUSH PRIVILEGES;"

wp core download --path="$WORDPRESS_PATH" --allow-root
wp config create --path="$WORDPRESS_PATH" --dbname="$WORDPRESS_DB_NAME" --dbuser="$WORDPRESS_DB_USER" --dbpass="$WORDPRESS_DB_PASSWORD" --dbhost="$WORDPRESS_DB_HOST" --skip-check --allow-root
wp core install --path="$WORDPRESS_PATH" --url="$WORDPRESS_URL" --title="Laravel WordPress Docker Test" --admin_user="$WORDPRESS_ADMIN_USER" --admin_password="$WORDPRESS_ADMIN_PASSWORD" --admin_email="$WORDPRESS_ADMIN_EMAIL" --skip-email --allow-root

wp rewrite structure '/%postname%/' --path="$WORDPRESS_PATH" --hard --allow-root
wp rewrite flush --path="$WORDPRESS_PATH" --hard --allow-root
wp post delete 1 --force --path="$WORDPRESS_PATH" --allow-root >/dev/null 2>&1 || true

echo "==> Starting WordPress PHP server"
pkill -f "php -S 0.0.0.0:8080" >/dev/null 2>&1 || true
cat > /tmp/wordpress-router.php <<'PHP'
<?php

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file = __DIR__.'/wordpress'.$path;
if ($path !== false && is_file($file)) {
    return false;
}

require __DIR__.'/wordpress/index.php';
PHP
ln -sfn "$WORDPRESS_PATH" /tmp/wordpress
php -S 0.0.0.0:8080 -t "$WORDPRESS_PATH" /tmp/wordpress-router.php >/tmp/wordpress-test-server.log 2>&1 &

until curl -fsS "$WORDPRESS_URL/wp-json" >/dev/null 2>&1; do
  sleep 2
done

wp user application-password create "$WORDPRESS_ADMIN_USER" laravel-wordpress-docker --path="$WORDPRESS_PATH" --porcelain --allow-root > /tmp/laravel-wordpress-app-password

echo "==> Seeding WordPress records and media"
author_id="$(wp user create integration_author author@example.com --role=author --display_name='Integration Author' --path="$WORDPRESS_PATH" --porcelain --allow-root | numeric_id)"
wp term create category 'Integration Category' --slug=integration-category --path="$WORDPRESS_PATH" --porcelain --allow-root >/dev/null
wp term create category 'Integration Secondary Category' --slug=integration-secondary-category --path="$WORDPRESS_PATH" --porcelain --allow-root >/dev/null
wp term create post_tag 'Integration Tag' --slug=integration-tag --path="$WORDPRESS_PATH" --porcelain --allow-root >/dev/null
wp term create post_tag 'Integration Secondary Tag' --slug=integration-secondary-tag --path="$WORDPRESS_PATH" --porcelain --allow-root >/dev/null
require_id author "$author_id"

mkdir -p /work/media-fixtures
base64 -d > /work/media-fixtures/featured.jpg <<'IMG'
/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAP//////////////////////////////////////////////////////////////////////////////////////2wBDAf//////////////////////////////////////////////////////////////////////////////////////wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAX/xAAVEAEBAAAAAAAAAAAAAAAAAAAAAP/aAAwDAQACEAMQAAABn//EABQQAQAAAAAAAAAAAAAAAAAAAAD/2gAIAQEAAQUCf//EABQRAQAAAAAAAAAAAAAAAAAAAAD/2gAIAQMBAT8BP//EABQRAQAAAAAAAAAAAAAAAAAAAAD/2gAIAQIBAT8BP//Z
IMG
base64 -d > /work/media-fixtures/inline.png <<'IMG'
iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII=
IMG

featured_id="$(wp media import /work/media-fixtures/featured.jpg --title='Integration Featured Image' --alt='Featured alt text' --path="$WORDPRESS_PATH" --porcelain --allow-root | numeric_id)"
inline_id="$(wp media import /work/media-fixtures/inline.png --title='Integration Inline Image' --alt='Inline alt text' --path="$WORDPRESS_PATH" --porcelain --allow-root | numeric_id)"
require_id featured_media "$featured_id"
require_id inline_media "$inline_id"
inline_url="$(wp post get "$inline_id" --field=guid --path="$WORDPRESS_PATH" --allow-root)"

post_id="$(wp post create --post_type=post --post_status=publish --post_author="$author_id" --post_title='Docker Integration Original Post' --post_excerpt='Original integration excerpt' --post_content="<p>Original integration content.</p><figure><img src=\"$inline_url\" alt=\"Inline alt text\" /></figure>" --porcelain --path="$WORDPRESS_PATH" --allow-root | numeric_id)"
require_id post "$post_id"

wp post term add "$post_id" category integration-category --path="$WORDPRESS_PATH" --allow-root
wp post term add "$post_id" post_tag integration-tag --path="$WORDPRESS_PATH" --allow-root
category_id="$(wp post term list "$post_id" category --fields=term_id,slug --format=csv --path="$WORDPRESS_PATH" --allow-root | awk -F, '$2 == "integration-category" { print $1 }' | numeric_id)"
tag_id="$(wp post term list "$post_id" post_tag --fields=term_id,slug --format=csv --path="$WORDPRESS_PATH" --allow-root | awk -F, '$2 == "integration-tag" { print $1 }' | numeric_id)"
require_id category "$category_id"
require_id tag "$tag_id"
wp post meta add "$post_id" integration_meta original --path="$WORDPRESS_PATH" --allow-root
wp post meta add "$post_id" _thumbnail_id "$featured_id" --path="$WORDPRESS_PATH" --allow-root

secondary_post_id="$(wp post create --post_type=post --post_status=publish --post_author="$author_id" --post_name=docker-integration-secondary-post --post_title='Docker Integration Secondary Post' --post_excerpt='Secondary integration excerpt with distinct taxonomy data.' --post_content='<p>Secondary integration content with a separate category and tag.</p>' --porcelain --path="$WORDPRESS_PATH" --allow-root | numeric_id)"
require_id secondary_post "$secondary_post_id"

wp post term add "$secondary_post_id" category integration-secondary-category --path="$WORDPRESS_PATH" --allow-root
wp post term add "$secondary_post_id" post_tag integration-secondary-tag --path="$WORDPRESS_PATH" --allow-root
secondary_category_id="$(wp post term list "$secondary_post_id" category --fields=term_id,slug --format=csv --path="$WORDPRESS_PATH" --allow-root | awk -F, '$2 == "integration-secondary-category" { print $1 }' | numeric_id)"
secondary_tag_id="$(wp post term list "$secondary_post_id" post_tag --fields=term_id,slug --format=csv --path="$WORDPRESS_PATH" --allow-root | awk -F, '$2 == "integration-secondary-tag" { print $1 }' | numeric_id)"
require_id secondary_category "$secondary_category_id"
require_id secondary_tag "$secondary_tag_id"
wp post meta add "$secondary_post_id" integration_meta secondary --path="$WORDPRESS_PATH" --allow-root

wp post create --post_type=page --post_status=publish --post_title='Docker Integration Page' --post_content='<p>Page content generated by the Docker integration setup.</p>' --path="$WORDPRESS_PATH" --allow-root >/dev/null

cat > /tmp/wordpress-seed.env <<SEED
WORDPRESS_SEED_AUTHOR_ID=$author_id
WORDPRESS_SEED_CATEGORY_ID=$category_id
WORDPRESS_SEED_SECONDARY_CATEGORY_ID=$secondary_category_id
WORDPRESS_SEED_TAG_ID=$tag_id
WORDPRESS_SEED_SECONDARY_TAG_ID=$secondary_tag_id
WORDPRESS_SEED_FEATURED_MEDIA_ID=$featured_id
WORDPRESS_SEED_INLINE_MEDIA_ID=$inline_id
WORDPRESS_SEED_POST_ID=$post_id
WORDPRESS_SEED_SECONDARY_POST_ID=$secondary_post_id
SEED

echo "==> WordPress seed posts: $post_id, $secondary_post_id"
