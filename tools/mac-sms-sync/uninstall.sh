#!/bin/bash
# Stops and removes the background agent and the binary.
# --purge also removes the config, the sync position, the log and the token in the Keychain.
set -euo pipefail

LABEL="dev.hisabi.sms-sync"

launchctl bootout "gui/$(id -u)/$LABEL" 2>/dev/null || true
rm -f "$HOME/Library/LaunchAgents/$LABEL.plist" "$HOME/.local/bin/hisabi-sms-sync"
echo "Removed the agent and $HOME/.local/bin/hisabi-sms-sync."

if [ "${1:-}" = "--purge" ]; then
    rm -rf "$HOME/Library/Application Support/HisabiSmsSync"
    rm -f "$HOME/Library/Logs/HisabiSmsSync.log" "$HOME/Library/Logs/HisabiSmsSync.log.1"
    security delete-generic-password -s "$LABEL" -a token >/dev/null 2>&1 || true
    echo "Removed the config, the sync position, the log and the token."
else
    echo "Kept the config and the sync position (~/Library/Application Support/HisabiSmsSync); --purge removes them."
fi

echo
echo "Also remove hisabi-sms-sync from System Settings > Privacy & Security > Full Disk Access,"
echo "and on the server: php artisan hisabi:sms-sync-token mac --revoke"
