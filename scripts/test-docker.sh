#!/usr/bin/env bash
set -euo pipefail

COMPOSE_FILE="${COMPOSE_FILE:-docker-compose.test.yml}"

echo "==> Building Docker integration test image"
docker compose -f "$COMPOSE_FILE" build app

echo "==> Starting database"
docker compose -f "$COMPOSE_FILE" up -d db

echo "==> Running fresh Laravel + WordPress integration workflow"
set +e
docker compose -f "$COMPOSE_FILE" run --rm app bash -lc '
  set -euo pipefail
  cd "$PACKAGE_PATH"
  mkdir -p artifacts
  bash scripts/setup-laravel-test-app.sh
  bash scripts/setup-wordpress-test.sh
  cd "$LARAVEL_APP_PATH"
  php artisan test --testsuite=Feature --log-junit "$PACKAGE_PATH/artifacts/junit.xml"
'
status=$?
set -e

echo "==> Stopping Docker services"
docker compose -f "$COMPOSE_FILE" down --remove-orphans

if [[ -f artifacts/integration-summary.txt ]]; then
  echo
  cat artifacts/integration-summary.txt
fi

if [[ -f artifacts/integration-report.json ]]; then
  echo
  echo "JSON report: artifacts/integration-report.json"
fi

exit "$status"
