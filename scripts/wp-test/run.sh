#!/usr/bin/env bash
set -euo pipefail
export WORDPRESS_TEST_BASE_URL=http://localhost:8088
export WORDPRESS_TEST_USERNAME=admin
export WORDPRESS_TEST_PASSWORD="$(cat /tmp/laravel-wordpress-app-password 2>/dev/null || echo password)"
composer test:integration
