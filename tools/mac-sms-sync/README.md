# Hisabi SMS sync for Mac

If your iPhone's messages sync to your Mac (Messages in iCloud), this small background tool sends new bank SMS to your Hisabi server as they arrive. Hisabi turns them into transactions the same way it does when you paste them, and never adds the same message twice.

- Only messages from the sender ids you list (your banks) ever leave the Mac.
- One-time codes (OTP, verification codes) are kept on the Mac even when they come from your bank. You can add your own patterns for other messages to keep, such as statement reminders or offers.
- The token lives in your Keychain. The log records counts and errors, never message text or the token.
- It reads `~/Library/Messages/chat.db` read-only.

It's a Swift command-line tool, so you give Full Disk Access to just this one binary rather than to Terminal.

## Setup

You need the Xcode Command Line Tools (`xcode-select --install`) to build it. Times are your Mac's local time.

1. **Create a token on the server** (where Hisabi runs, e.g. `docker-compose exec app` in front):

   ```bash
   php artisan hisabi:sms-sync-token mac
   ```

   It prints the token once. Running it again replaces the old one.

2. **Build and install the binary** (into `~/.local/bin`, no sudo):

   ```bash
   cd tools/mac-sms-sync
   ./install.sh
   ```

3. **Save the token** in your Keychain (paste it when asked; it won't show):

   ```bash
   ~/.local/bin/hisabi-sms-sync set-token
   ```

4. **Give the binary Full Disk Access**: System Settings > Privacy & Security > Full Disk Access, click +, press Cmd+Shift+G, type `~/.local/bin/hisabi-sms-sync` and add it.

   macOS gives that access to the binary when launchd starts it. When you run a command from Terminal, it's Terminal that would need the access, so the commands that read Messages hand themselves to launchd and show you the result. You don't need to give Terminal anything.

5. **Find your banks' sender ids**:

   ```bash
   ~/.local/bin/hisabi-sms-sync list-senders
   ```

   It lists the names messages came from (like `ExampleBank`), with how many and the latest date. Phone numbers and email addresses aren't listed.

6. **Edit the config** that `list-senders` created, `~/Library/Application Support/HisabiSmsSync/config.json`, with your server and your banks' ids:

   ```json
   {
     "server": "https://hisabi.example.com",
     "senders": ["ExampleBank", "SampleBank"],
     "skipPatterns": ["statement is ready", "special offer"]
   }
   ```

   `server` must be `https://` (plain `http://` only works for addresses on your own network). `skipPatterns` are regular expressions, matched ignoring case.

7. **See what would be sent**, without sending anything:

   ```bash
   ~/.local/bin/hisabi-sms-sync run --dry-run --since "2026-11-15 13:30"
   ```

   Choose the start so nothing you already have in Hisabi is sent again. If your transactions were imported up to 15 Nov 2026 at 13:30, start there: nothing received before `--since` is ever sent.

8. **Send them**, the first time with the same start:

   ```bash
   ~/.local/bin/hisabi-sms-sync run --since "2026-11-15 13:30"
   ```

9. **Start the background agent**:

   ```bash
   ./install.sh
   ```

   From now on it runs when Messages changes, and every 10 minutes in case a change was missed (at most once a minute).

Check everything with `~/.local/bin/hisabi-sms-sync status`. `self-test` checks the parts that don't need your messages.

## How duplicates are prevented

Sending again is always safe, so the tool resends rather than risks missing a message.

1. **The same message sent twice** (a retry, a restart, two Macs, Messages re-downloading from iCloud): each message carries its Messages id, which the server stores once (a unique index) and answers `duplicate` for afterwards.
2. **The same text pasted by hand and synced**, in either order, or the bank sending the same SMS twice: the server compares the text with its whitespace collapsed. Bank SMS carry a balance or a time, so the same text is the same event. Pasting an SMS that was already synced says "already added".
3. **Two requests at the same moment**: the unique index lets only one store the message; the other gets `duplicate` and its transaction is rolled back.

On the Mac, the tool remembers the last message the server confirmed (`state.json`) and only moves on once the server has answered. If the server can't be reached, it tries again on the next run.

Messages without their own date (some banks leave it out) are dated when they arrived on the phone, in the server's time zone. Messages with a date keep it.

**Monthly statements:** once the sync is on, card purchases are already in Hisabi when the statement arrives. When you import a statement, match its lines to the transactions the SMS created (`sms.transaction_id` links them) and correct the amount or date, instead of inserting them again.

## The log

```bash
tail -f ~/Library/Logs/HisabiSmsSync.log
```

One line per run that found something, for example `Sent 3: 2 new transaction(s), 1 without a transaction, 0 already there. Kept on the Mac: 1 one-time code(s).`, and any errors (a rejected token, the server unreachable, no Full Disk Access).

## Updating

Pull the code and run `./install.sh` again. If the binary changed, macOS treats it as a new app: give the new one Full Disk Access again (remove the old entry and add it), and choose Always Allow when it asks about the Keychain.

## Uninstall

```bash
./uninstall.sh          # stop the agent, remove the binary
./uninstall.sh --purge  # also remove the config, the sync position, the log and the token
```

Then remove `hisabi-sms-sync` from Full Disk Access, and revoke the token on the server: `php artisan hisabi:sms-sync-token mac --revoke`.

## The server side

`POST /api/v1/sms/sync` with `Authorization: Bearer <token>` and up to 100 messages:

```json
{ "messages": [{ "guid": "…", "sender": "ExampleBank", "received_at": "2026-11-15T13:30:00+04:00", "text": "…" }] }
```

Each comes back as `created` (with the transaction id, or null when the message isn't a transaction or wasn't recognised) or `duplicate`.
