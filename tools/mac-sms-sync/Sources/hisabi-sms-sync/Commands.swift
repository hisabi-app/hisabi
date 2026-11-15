import Foundation

/// `run`: send the bank SMS received since the last run.
enum SyncCommand {
    static let chunkSize = 200
    /// The server takes up to 10,000 characters; bank SMS are far shorter.
    static let maxLength = 10_000

    struct Counts {
        var transactions = 0
        var withoutTransaction = 0
        var duplicates = 0
        var codes = 0
        var skipped = 0
        var unreadable = 0
        var wouldSend = 0

        var sent: Int { transactions + withoutTransaction + duplicates }
        var isEmpty: Bool { sent + codes + skipped + unreadable == 0 }

        var summary: String {
            var parts = ["Sent \(sent): \(transactions) new transaction(s), \(withoutTransaction) without a transaction, \(duplicates) already there."]

            let kept = [
                codes > 0 ? "\(codes) one-time code(s)" : nil,
                skipped > 0 ? "\(skipped) matching a skip pattern" : nil,
                unreadable > 0 ? "\(unreadable) with no readable text" : nil,
            ].compactMap { $0 }

            if !kept.isEmpty {
                parts.append("Kept on the Mac: " + kept.joined(separator: ", ") + ".")
            }

            return parts.joined(separator: " ")
        }
    }

    static func run(options: [String], arguments: [String]) throws -> Int32 {
        var since: Date?
        var dryRun = false
        var index = 0

        while index < options.count {
            switch options[index] {
            case "--dry-run":
                dryRun = true
            case "--since":
                guard index + 1 < options.count, let date = Dates.parseSince(options[index + 1]) else {
                    throw Failure("--since needs a date like \"2026-01-31 13:30\" (this Mac's time).")
                }
                since = date
                index += 1
            default:
                throw Failure("Unknown option for run: \(options[index])")
            }

            index += 1
        }

        Log.toFile = !dryRun

        let config = try Config.load()

        if let problem = config.problem {
            throw Failure(problem)
        }

        let token = dryRun ? "" : try Keychain.token()

        guard var state = try since.map({ SyncState(startDate: $0, lastRowID: 0, lastMessageDate: nil) }) ?? SyncState.load() else {
            throw Failure("Say where to start the first time: hisabi-sms-sync run --since \"YYYY-MM-DD HH:MM\". Nothing received before that is ever sent.")
        }

        if let exitCode = try MessagesAccess.ensureReadable(arguments: arguments) {
            return exitCode
        }

        let lock = dryRun ? nil : try RunLock.acquire()

        if !dryRun && lock == nil {
            print("Another run is in progress.")
            return 0
        }

        let database = try ChatDatabase(url: Paths.messages)
        let server = Server(endpoint: config.endpoint, token: token)
        let skipExpressions = config.skipExpressions
        var counts = Counts()
        var floor = state.startDate

        if try database.newestRowID() < state.lastRowID {
            // Messages rebuilt its database and numbered the messages again. Read again from a day
            // before the last message sent; the server recognises the ones it already has.
            state.lastRowID = 0
            floor = max(floor, (state.lastMessageDate ?? floor).addingTimeInterval(-86_400))
            Log.line("The Messages database was rebuilt; reading again from \(Dates.local(floor)).")
        }

        if dryRun {
            print("Dry run: nothing is sent. Reading messages from \(Dates.local(floor)).\n")
        }

        reading: while true {
            let messages = try database.incoming(after: state.lastRowID, since: floor, limit: chunkSize)
            var outgoing: [OutgoingMessage] = []
            var lastRowID = state.lastRowID
            var lastMessageDate = state.lastMessageDate
            var waitingForText = false

            for message in messages {
                guard config.isAllowed(sender: message.sender) else {
                    lastRowID = message.rowID
                    continue
                }

                let text = MessageText.from(text: message.text, attributedBody: message.attributedBody)?
                    .trimmingCharacters(in: .whitespacesAndNewlines) ?? ""

                if text.isEmpty {
                    // Just arrived: its text may still be on its way, so stop here and look again next run
                    if message.date > Date().addingTimeInterval(-600) {
                        waitingForText = true
                        break
                    }

                    counts.unreadable += 1
                } else if Filters.isOneTimeCode(text) {
                    counts.codes += 1
                    if dryRun { print("\(Dates.local(message.date))  \(message.sender)  (one-time code, kept on the Mac)\n") }
                } else if Filters.matchesAny(skipExpressions, text) || text.count > maxLength {
                    counts.skipped += 1
                    if dryRun { print("\(Dates.local(message.date))  \(message.sender)  (matches a skip pattern, kept on the Mac)\n") }
                } else {
                    outgoing.append(OutgoingMessage(
                        guid: message.guid,
                        sender: message.sender,
                        received_at: Dates.iso8601(message.date),
                        text: text
                    ))
                    lastMessageDate = max(lastMessageDate ?? message.date, message.date)
                    if dryRun { print("\(Dates.local(message.date))  \(message.sender)\n\(text)\n") }
                }

                lastRowID = message.rowID
            }

            if dryRun {
                counts.wouldSend += outgoing.count
            } else {
                // If a batch fails, nothing in this chunk counts as sent: it's all sent again next run,
                // and the server recognises what it already has
                for start in stride(from: 0, to: outgoing.count, by: Server.batchSize) {
                    let batch = Array(outgoing[start..<min(start + Server.batchSize, outgoing.count)])

                    for result in try server.send(batch) {
                        switch (result.status, result.transaction_id) {
                        case ("duplicate", _): counts.duplicates += 1
                        case (_, .some): counts.transactions += 1
                        default: counts.withoutTransaction += 1
                        }
                    }
                }
            }

            state.lastRowID = lastRowID
            state.lastMessageDate = lastMessageDate

            if !dryRun {
                try state.save()
            }

            if waitingForText || messages.count < chunkSize {
                break reading
            }
        }

        if !dryRun {
            // The first run saves where it started even when there was nothing to send
            try state.save()
        }

        withExtendedLifetime(lock) {}

        if dryRun {
            print("Would send \(counts.wouldSend) message(s)." + (counts.codes + counts.skipped > 0 ? " Would keep \(counts.codes + counts.skipped) on the Mac." : ""))
        } else if counts.isEmpty {
            Log.line("Nothing new.", file: false)
        } else {
            Log.line(counts.summary)
        }

        return 0
    }
}

/// `list-senders`: the alphanumeric sender ids you received messages from, to pick your banks.
enum SendersCommand {
    static func run(arguments: [String]) throws -> Int32 {
        let config = try? Config.load()

        if let exitCode = try MessagesAccess.ensureReadable(arguments: arguments) {
            return exitCode
        }

        let senders = try ChatDatabase(url: Paths.messages).senders().filter { Filters.isAlphanumericSender($0.sender) }

        guard !senders.isEmpty else {
            print("No messages from sender ids (like a bank's name) yet.")
            return 0
        }

        let width = max(senders.map(\.sender.count).max() ?? 0, 6)
        print("Sender".padding(toLength: width, withPad: " ", startingAt: 0) + "  Messages  Latest")

        for (sender, count, last) in senders {
            let syncing = config?.isAllowed(sender: sender) == true ? "  (syncing)" : ""
            print(sender.padding(toLength: width, withPad: " ", startingAt: 0) + "  " + String(count).padding(toLength: 8, withPad: " ", startingAt: 0) + "  " + Dates.local(last, withTime: false) + syncing)
        }

        print("\nPut your banks' ids in \"senders\" in \(Paths.display(Paths.config)). Phone numbers and email addresses aren't listed.")

        return 0
    }
}

/// `set-token`: save the server token in the Keychain.
enum TokenCommand {
    static func run() throws -> Int32 {
        var token: String

        if isatty(STDIN_FILENO) != 0 {
            var buffer = [CChar](repeating: 0, count: 1024)

            guard let entered = readpassphrase("Paste the token from php artisan hisabi:sms-sync-token (it won't show): ", &buffer, buffer.count, 0) else {
                throw Failure("Couldn't read the token.")
            }

            token = String(cString: entered)
            buffer.withUnsafeMutableBufferPointer { $0.update(repeating: 0) }
        } else {
            token = readLine() ?? ""
        }

        token = token.trimmingCharacters(in: .whitespacesAndNewlines)

        guard token.range(of: #"^[0-9]+\|[A-Za-z0-9]+$"#, options: .regularExpression) != nil else {
            throw Failure("That doesn't look like a token from php artisan hisabi:sms-sync-token (a number, a | and letters and digits).")
        }

        try Keychain.save(token)
        print("Saved in your Keychain.")

        return 0
    }
}

/// `status`: what's set up and what isn't. Exits 0 once everything is ready.
enum StatusCommand {
    static func run() -> Int32 {
        var ready = true

        func show(_ label: String, _ value: String) {
            print(label.padding(toLength: 10, withPad: " ", startingAt: 0) + value)
        }

        var config: Config?

        do {
            config = try Config.load()
            show("Config", Paths.display(Paths.config) + (config?.problem.map { "\n          " + $0 } ?? ""))
            ready = ready && config?.problem == nil
        } catch {
            show("Config", "\(error)")
            ready = false
        }

        if let config {
            show("Server", config.server)
            show("Senders", config.senders.isEmpty ? "none yet" : config.senders.joined(separator: ", "))
        }

        let hasToken = Keychain.hasToken()
        show("Token", hasToken ? "saved in the Keychain" : "missing: run hisabi-sms-sync set-token")
        ready = ready && hasToken

        if let config, config.problem == nil, hasToken, let token = try? Keychain.token() {
            show("", "the server " + Server(endpoint: config.endpoint, token: token).checkToken())
        }

        let descriptor = open(Paths.messages.path, O_RDONLY)
        if descriptor >= 0 { close(descriptor) }
        show("Messages", descriptor >= 0 ? "readable" : "not readable from here (fine if the binary has Full Disk Access: launchd runs it)")

        if let state = try? SyncState.load() {
            show("Sync", "from \(Dates.local(state.startDate))" + (state.lastMessageDate.map { ", last message sent \(Dates.local($0))" } ?? ", nothing sent yet"))
        } else {
            show("Sync", "not started: run hisabi-sms-sync run --since \"YYYY-MM-DD HH:MM\"")
            ready = false
        }

        show("Agent", launchctl(["print", "gui/\(getuid())/\(Paths.agentLabel)"]) == 0 ? "running in the background" : "not installed (./install.sh)")
        show("Log", Paths.display(Paths.log))

        return ready ? 0 : 1
    }
}
