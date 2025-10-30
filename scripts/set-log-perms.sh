#!/usr/bin/env bash
# Set permissions for plugin debug logs (Linux)
# Usage: sudo ./set-log-perms.sh /path/to/wordpress www-data

LOG_DIR="${1:-/var/www/html/wp-content}"
WEBUSER="${2:-www-data}"

DEBUG_LOG="$LOG_DIR/debug.log"
PLUGIN_LOG="$LOG_DIR/repro-ct-suite-debug.log"

if [ "$EUID" -ne 0 ]; then
  echo "This script should be run as root (or with sudo) to change ownership/SELinux contexts."
fi

echo "Ensuring log files exist..."
mkdir -p "$LOG_DIR"
[ -f "$DEBUG_LOG" ] || touch "$DEBUG_LOG"
[ -f "$PLUGIN_LOG" ] || touch "$PLUGIN_LOG"

echo "Setting owner to $WEBUSER and permissions to 660"
chown $WEBUSER:$WEBUSER "$DEBUG_LOG" "$PLUGIN_LOG"
chmod 660 "$DEBUG_LOG" "$PLUGIN_LOG"

# If SELinux is enabled, set context
if command -v getenforce >/dev/null 2>&1; then
  SEL=$(getenforce)
  if [ "$SEL" = "Enforcing" ] || [ "$SEL" = "Permissive" ]; then
    echo "SELinux detected ($SEL). Setting httpd_sys_rw_content_t context."
    chcon -t httpd_sys_rw_content_t "$DEBUG_LOG" || true
    chcon -t httpd_sys_rw_content_t "$PLUGIN_LOG" || true
  fi
fi

echo "Done. Files:"
ls -l "$DEBUG_LOG" "$PLUGIN_LOG" || true
