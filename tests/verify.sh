#!/usr/bin/env bash
set -Eeuo pipefail
cd "$(dirname "$0")/.."
command -v docker >/dev/null || { echo 'BLOCKER: Docker CLI unavailable.' >&2; exit 1; }
docker info >/dev/null 2>&1 || { echo 'BLOCKER: Docker daemon unavailable or its socket is inaccessible.' >&2; exit 1; }
docker compose version >/dev/null || { echo 'BLOCKER: Docker Compose unavailable.' >&2; exit 1; }
# A unique project, explicit files, and private credentials isolate every invocation.
project="lol-verify-$(date +%s)-$$-$RANDOM"
export DB_NAME=litigation_test DB_USER=litigation_test
export DB_PASSWORD="test-$RANDOM-$RANDOM" DB_ROOT_PASSWORD="root-$RANDOM-$RANDOM"
export DEV_PASSWORD=smoke-player-password
unset COMPOSE_FILE COMPOSE_PROJECT_NAME COMPOSE_PROFILES
compose=(docker compose --env-file /dev/null -p "$project" -f compose.yaml -f tests/compose.yaml)
cleanup() {
    status=$?
    trap - EXIT
    if (( status != 0 )); then "${compose[@]}" logs --no-color >&2 || true; fi
    "${compose[@]}" down --volumes --remove-orphans || status=1
    exit "$status"
}
trap cleanup EXIT
"${compose[@]}" up --build -d --wait --wait-timeout 180
"${compose[@]}" exec -T web sh -ec 'find . -type f \( -name "*.php" -o -name "*.inc" \) > /tmp/php-files; while IFS= read -r file; do php -l "$file" || exit 1; done < /tmp/php-files'
"${compose[@]}" exec -T web php scripts/seed-development.php
"${compose[@]}" exec -T web php tests/smoke.php
logs=$("${compose[@]}" logs --no-color web)
if printf '%s\n' "$logs" | grep -Ei 'PHP (Warning|Deprecated|Fatal|Parse|Notice)|SQLSTATE'; then
    echo 'Runtime diagnostics found.' >&2
    exit 1
fi
echo 'PASS: PHP lint and database/HTTP smoke tests.'
