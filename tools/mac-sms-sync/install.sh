#!/bin/bash
# Builds hisabi-sms-sync, installs it in ~/.local/bin (no sudo) and, once the setup in README.md
# is done (the first `run --since …` has happened), installs and starts the background agent.
set -euo pipefail

cd "$(dirname "$0")"

LABEL="dev.hisabi.sms-sync"
BIN_DIR="$HOME/.local/bin"
BIN="$BIN_DIR/hisabi-sms-sync"
PLIST="$HOME/Library/LaunchAgents/$LABEL.plist"
STATE="$HOME/Library/Application Support/HisabiSmsSync/state.json"
DOMAIN="gui/$(id -u)"

echo "Building…"
swift build -c release --product hisabi-sms-sync
BUILT="$(swift build -c release --show-bin-path)/hisabi-sms-sync"

mkdir -p "$BIN_DIR"

if [ -f "$BIN" ] && cmp -s "$BUILT" "$BIN"; then
    echo "$BIN is up to date."
else
    REPLACED=$([ -f "$BIN" ] && echo yes || echo no)
    cp "$BUILT" "$BIN.new"
    chmod 755 "$BIN.new"
    mv "$BIN.new" "$BIN"
    echo "Installed $BIN"

    if [ "$REPLACED" = yes ]; then
        echo
        echo "The binary changed, so macOS treats it as a new app:"
        echo "  - System Settings > Privacy & Security > Full Disk Access: remove hisabi-sms-sync and add $BIN again."
        echo "  - The first time it reads the token, macOS asks about the Keychain: choose Always Allow."
    fi
fi

"$BIN" self-test

case ":$PATH:" in
    *":$BIN_DIR:"*) ;;
    *) echo "Tip: add $BIN_DIR to your PATH to type just hisabi-sms-sync." ;;
esac

if [ ! -f "$STATE" ]; then
    echo
    echo "Next: finish the setup in README.md (token, Full Disk Access, senders, first run),"
    echo "then run ./install.sh again to start the background agent."
    exit 0
fi

mkdir -p "$HOME/Library/LaunchAgents" "$HOME/Library/Logs"
sed -e "s|__BINARY__|$BIN|g" -e "s|__HOME__|$HOME|g" "$LABEL.plist" > "$PLIST"

launchctl bootout "$DOMAIN/$LABEL" 2>/dev/null || true
launchctl bootstrap "$DOMAIN" "$PLIST"

echo
echo "The agent is running. It sends new bank SMS when Messages changes, and every 10 minutes."
echo "Log: ~/Library/Logs/HisabiSmsSync.log"
