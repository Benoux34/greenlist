#!/usr/bin/env bash
# Deploys whatever is on origin/main. Run it on the VPS, or let the deploy job
# of .github/workflows/ci.yaml trigger it over SSH.
#
# --build runs every time on purpose: unchanged layers come from the cache in
# seconds, and nobody has to decide whether a change needs a rebuild.
set -euo pipefail

cd "$(dirname "$0")/.."

COMPOSE=(docker compose -f docker-compose.prod.yml --env-file .env.prod)

echo "==> Pulling"
git pull --ff-only

echo "==> Building and starting"
"${COMPOSE[@]}" up -d --build

# There is no health endpoint: an anonymous call to /api/me must come back as
# 401. That proves nginx reaches Symfony, and Symfony only starts listening
# once the migrations have run, so the database was reachable too.
echo "==> Waiting for the API"
status=none
for _ in $(seq 1 30); do
  status=$(curl -s -o /dev/null -m 5 -w '%{http_code}' http://127.0.0.1:8081/api/me || true)
  if [ "$status" = "401" ]; then
    echo "==> Up. Deployed $(git rev-parse --short HEAD)."
    exit 0
  fi
  sleep 2
done

echo "!! API still not answering after 60s (last status: $status). Last logs:" >&2
"${COMPOSE[@]}" logs --tail 40 api >&2
exit 1
