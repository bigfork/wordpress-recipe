#!/usr/bin/env bash
#
# Clears OPcache on the PHP-FPM pool that actually serves requests.
#
# `wp eval-file` (see deploy-wp-rocket.php) runs inside WP-CLI's own PHP CLI process, so calling
# opcache_reset() there only clears the CLI's cache, not FPM's. The deploy user also can't run
# `service php-fpm reload`. So instead: drop a one-off script into the webroot, hit it over HTTP
# so it executes inside the real FPM pool, then delete it.
#
# The domain is read from WP-CLI (home_url()) rather than hardcoded — .env can't be parsed
# directly since Bedrock's dotenv supports ${VAR} expansion (e.g. DDEV's WP_HOME="https://${DDEV_HOSTNAME}").
# The request is aimed at 127.0.0.1 via --resolve so it never depends on public DNS.
#
# Requests can 404 with "No input file specified" for a few seconds right after activation: the
# long-lived FPM workers keep their own realpath cache of where the `current` symlink points, and
# don't necessarily re-check it on every request, so a worker can keep resolving into the release
# this deploy just replaced. We retry briefly rather than fail outright on that.
#
# Run from Envoyer once the new release has been activated:
#
#     cd {{ release }}
#     bash util/deploy-opcache-clear.sh

set -euo pipefail

WP_HOME=$(wp eval 'echo home_url();')
SCHEME=$(echo "$WP_HOME" | sed -E 's#^(https?)://.*#\1#')
HOST=$(echo "$WP_HOME" | sed -E 's#^https?://##; s#/.*##; s#:.*##')
PORT=80
[ "$SCHEME" = "https" ] && PORT=443

TOKEN=$(openssl rand -hex 16)
CLEAR_FILE="public/opcache-clear-${TOKEN}.php"

cleanup() {
    rm -f "$CLEAR_FILE"
}
trap cleanup EXIT

cat > "$CLEAR_FILE" <<'PHP'
<?php
if (function_exists('opcache_reset') && opcache_reset()) {
    echo 'opcache cleared';
} else {
    http_response_code(500);
    echo 'opcache_reset unavailable';
}
PHP

# -k: we're hitting 127.0.0.1 directly, bypassing anything in front of the site (Cloudflare,
# etc.), so the cert Apache presents locally often won't validate for this hostname. That's fine
# here — the token in the URL is what pins this request to our own file, not TLS.
URL="${SCHEME}://${HOST}/opcache-clear-${TOKEN}.php"
ATTEMPTS=5
for i in $(seq 1 "$ATTEMPTS"); do
    set +e
    STATUS=$(curl -sk -o /tmp/opcache-clear-response.$$ -w '%{http_code}' \
        --resolve "${HOST}:${PORT}:127.0.0.1" "$URL")
    CURL_EXIT=$?
    set -e

    RESPONSE=$(cat /tmp/opcache-clear-response.$$ 2>/dev/null || true)
    rm -f /tmp/opcache-clear-response.$$

    if [ "$CURL_EXIT" -eq 0 ] && [ "$STATUS" = "200" ]; then
        echo "OPcache cleared on ${HOST}: ${RESPONSE}"
        exit 0
    fi

    echo "Attempt ${i}/${ATTEMPTS} failed (curl exit ${CURL_EXIT}, HTTP ${STATUS}): ${RESPONSE}" >&2
    [ "$i" -lt "$ATTEMPTS" ] && sleep 2
done

echo "OPcache clear request failed after ${ATTEMPTS} attempts" >&2
exit 1
