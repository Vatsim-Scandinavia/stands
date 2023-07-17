#!/bin/bash
set -e

SELF_SIGNED_KEY=/etc/ssl/private/apache-selfsigned.key
SELF_SIGNED_CERT=/etc/ssl/certs/apache-selfsigned.crt

if [ ! -f "$SELF_SIGNED_KEY" ] || [ ! -f "$SELF_SIGNED_CERT" ]; then
    # Generate a self-signed cert to support SSL connections
    openssl req -x509 -nodes -days 358000 -newkey rsa:2048 -keyout "$SELF_SIGNED_KEY" -out "$SELF_SIGNED_CERT" -subj "/O=Your vACC/CN=Stands"
fi

exec docker-php-entrypoint "$@"
