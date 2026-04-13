#!/usr/bin/env bash
set -euo pipefail

PASS=0
FAIL=0
ROOT="$(cd "$(dirname "$0")/.." && pwd)"

run() {
    local label="$1"
    local file="$2"
    echo ""
    echo ">>> $label"
    if docker compose -f "$ROOT/docker-compose.yml" exec -T app php "$file"; then
        PASS=$((PASS + 1))
    else
        FAIL=$((FAIL + 1))
    fi
}

run "SmokeTest"               tests/SmokeTest.php
run "Unit: Validator"         tests/Unit/ValidatorTest.php
run "Unit: PasswordStrength"  tests/Unit/PasswordStrengthTest.php
run "Unit: RateLimiter"       tests/Unit/RateLimiterTest.php
run "Integration: LoginCsrf"  tests/Integration/LoginCsrfTest.php

echo ""
echo "========================================"
echo "Итого: $PASS пройдено, $FAIL провалено"
echo "========================================"

[ "$FAIL" -eq 0 ]
