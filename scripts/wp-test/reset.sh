#!/usr/bin/env bash
set -euo pipefail
bash scripts/wp-test/up.sh
until curl -fsS http://localhost:8088 >/dev/null 2>&1; do sleep 3; done
docker compose -f docker/wordpress/docker-compose.yml run --rm cli wp core install --url=http://localhost:8088 --title='Laravel WordPress Test' --admin_user=admin --admin_password=password --admin_email=admin@example.com --skip-email || true
docker compose -f docker/wordpress/docker-compose.yml run --rm cli wp rewrite structure '/%postname%/' --hard
docker compose -f docker/wordpress/docker-compose.yml run --rm cli wp rewrite flush --hard
docker compose -f docker/wordpress/docker-compose.yml run --rm cli wp user application-password create admin laravel-wordpress --porcelain > /tmp/laravel-wordpress-app-password
