#!/usr/bin/env bash
set -euo pipefail
docker compose -f docker/wordpress/docker-compose.yml up -d
