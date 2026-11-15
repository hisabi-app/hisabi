import Foundation

/// A problem the person running the tool can fix, said plainly.
struct Failure: Error, CustomStringConvertible {
    let description: String

    init(_ description: String) {
        self.description = description
    }
}

enum Paths {
    static let home: URL = {
        #if DEBUG
        // Debug builds can be pointed at a made-up home (config, state, log and a fake chat.db) for testing
        if let override = ProcessInfo.processInfo.environment["HISABI_SMS_SYNC_HOME"] {
            return URL(fileURLWithPath: override, isDirectory: true)
        }
        #endif
        return FileManager.default.homeDirectoryForCurrentUser
    }()

    static let support = home.appendingPathComponent("Library/Application Support/HisabiSmsSync", isDirectory: true)
    static let config = support.appendingPathComponent("config.json")
    static let state = support.appendingPathComponent("state.json")
    static let lock = support.appendingPathComponent("run.lock")
    static let log = home.appendingPathComponent("Library/Logs/HisabiSmsSync.log")
    static let messages = home.appendingPathComponent("Library/Messages/chat.db")

    static let agentLabel = "dev.hisabi.sms-sync"

    static func display(_ url: URL) -> String {
        let path = url.path
        return path.hasPrefix(home.path + "/") ? "~" + path.dropFirst(home.path.count) : path
    }

    static func ensureSupportDirectory() throws {
        try FileManager.default.createDirectory(at: support, withIntermediateDirectories: true)
    }
}

/// ~/Library/Application Support/HisabiSmsSync/config.json
struct Config: Codable {
    var server: String
    /// Sender ids as Messages shows them (see `list-senders`). Only their messages ever leave the Mac.
    var senders: [String]
    /// Regular expressions (case-insensitive) for messages to keep on the Mac, on top of one-time codes.
    var skipPatterns: [String]?

    static let example = Config(server: "https://hisabi.example.com", senders: [], skipPatterns: [])

    /// The config, or a Failure that says what to fill in. Writes an example config the first time.
    static func load() throws -> Config {
        guard FileManager.default.fileExists(atPath: Paths.config.path) else {
            try Paths.ensureSupportDirectory()
            let encoder = JSONEncoder()
            encoder.outputFormatting = [.prettyPrinted, .sortedKeys, .withoutEscapingSlashes]
            try encoder.encode(example).write(to: Paths.config)

            throw Failure("Created \(Paths.display(Paths.config)). Put your Hisabi address in \"server\" and your banks' sender ids (see list-senders) in \"senders\".")
        }

        do {
            return try JSONDecoder().decode(Config.self, from: Data(contentsOf: Paths.config))
        } catch {
            throw Failure("Couldn't read \(Paths.display(Paths.config)): \(error.localizedDescription)")
        }
    }

    /// What still needs filling in before messages can be sent, if anything.
    var problem: String? {
        if server == Config.example.server {
            return "Put your Hisabi address in \"server\" in \(Paths.display(Paths.config))."
        }

        if let problem = Filters.serverProblem(server) {
            return problem
        }

        if senders.isEmpty {
            return "Add your banks' sender ids to \"senders\" in \(Paths.display(Paths.config)) (see list-senders)."
        }

        for pattern in skipPatterns ?? [] where (try? NSRegularExpression(pattern: pattern)) == nil {
            return "This skip pattern isn't a valid regular expression: \(pattern)"
        }

        return nil
    }

    var endpoint: URL {
        let base = server.hasSuffix("/") ? String(server.dropLast()) : server
        return URL(string: base + "/api/v1/sms/sync")!
    }

    var skipExpressions: [NSRegularExpression] {
        (skipPatterns ?? []).compactMap { try? NSRegularExpression(pattern: $0, options: [.caseInsensitive]) }
    }

    func isAllowed(sender: String) -> Bool {
        senders.contains { $0.caseInsensitiveCompare(sender) == .orderedSame }
    }
}

/// ~/Library/Application Support/HisabiSmsSync/state.json: how far the sync got.
struct SyncState: Codable {
    /// Nothing received before this is ever sent (set by `run --since`).
    var startDate: Date
    /// The last message (chat.db ROWID) the server confirmed, or that was passed over.
    var lastRowID: Int64
    /// The newest message the server confirmed.
    var lastMessageDate: Date?

    static func load() throws -> SyncState? {
        guard FileManager.default.fileExists(atPath: Paths.state.path) else {
            return nil
        }

        let decoder = JSONDecoder()
        decoder.dateDecodingStrategy = .iso8601

        return try decoder.decode(SyncState.self, from: Data(contentsOf: Paths.state))
    }

    func save() throws {
        try Paths.ensureSupportDirectory()
        let encoder = JSONEncoder()
        encoder.dateEncodingStrategy = .iso8601
        encoder.outputFormatting = [.prettyPrinted, .sortedKeys]
        try encoder.encode(self).write(to: Paths.state, options: .atomic)
    }
}

/// Only one run at a time (the agent and a run started by hand).
final class RunLock {
    private let descriptor: Int32

    private init(descriptor: Int32) {
        self.descriptor = descriptor
    }

    /// nil when another run holds the lock.
    static func acquire() throws -> RunLock? {
        try Paths.ensureSupportDirectory()
        let descriptor = open(Paths.lock.path, O_CREAT | O_RDWR, 0o600)

        guard descriptor >= 0 else {
            throw Failure("Couldn't open \(Paths.display(Paths.lock)).")
        }

        guard flock(descriptor, LOCK_EX | LOCK_NB) == 0 else {
            close(descriptor)
            return nil
        }

        return RunLock(descriptor: descriptor)
    }

    deinit {
        flock(descriptor, LOCK_UN)
        close(descriptor)
    }
}

enum Log {
    /// `run` (not a dry run) also writes its lines to the log file. Never message text or the token.
    static var toFile = false

    static func line(_ message: String, file: Bool = true) {
        print(message)
        fflush(stdout)

        guard toFile, file else { return }

        let formatter = DateFormatter()
        formatter.locale = Locale(identifier: "en_US_POSIX")
        formatter.dateFormat = "yyyy-MM-dd HH:mm:ss"
        let entry = Data("\(formatter.string(from: Date())) \(message)\n".utf8)

        let manager = FileManager.default
        try? manager.createDirectory(at: Paths.log.deletingLastPathComponent(), withIntermediateDirectories: true)

        // Keep it small: one previous file at most
        if let size = (try? manager.attributesOfItem(atPath: Paths.log.path))?[.size] as? Int, size > 1_000_000 {
            let previous = Paths.log.appendingPathExtension("1")
            try? manager.removeItem(at: previous)
            try? manager.moveItem(at: Paths.log, to: previous)
        }

        if let handle = try? FileHandle(forWritingTo: Paths.log) {
            handle.seekToEndOfFile()
            handle.write(entry)
            try? handle.close()
        } else {
            try? entry.write(to: Paths.log)
        }
    }
}

enum Dates {
    static func local(_ date: Date, withTime: Bool = true) -> String {
        let formatter = DateFormatter()
        formatter.locale = Locale(identifier: "en_US_POSIX")
        formatter.dateFormat = withTime ? "yyyy-MM-dd HH:mm" : "yyyy-MM-dd"
        return formatter.string(from: date)
    }

    /// "2026-11-15T13:30:00+04:00": the time the message arrived, with this Mac's offset.
    static func iso8601(_ date: Date, timeZone: TimeZone = .current) -> String {
        let formatter = ISO8601DateFormatter()
        formatter.formatOptions = [.withInternetDateTime]
        formatter.timeZone = timeZone
        return formatter.string(from: date)
    }

    /// "YYYY-MM-DD HH:MM" or "YYYY-MM-DD", in this Mac's time zone.
    static func parseSince(_ text: String) -> Date? {
        let formatter = DateFormatter()
        formatter.locale = Locale(identifier: "en_US_POSIX")
        formatter.timeZone = .current

        for format in ["yyyy-MM-dd HH:mm", "yyyy-MM-dd'T'HH:mm", "yyyy-MM-dd"] {
            formatter.dateFormat = format
            if let date = formatter.date(from: text.trimmingCharacters(in: .whitespaces)) {
                return date
            }
        }

        return nil
    }
}
