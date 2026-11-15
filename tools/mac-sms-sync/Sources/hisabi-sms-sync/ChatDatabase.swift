import Foundation
import SQLite3
import TypedStream

/// An incoming message as Messages stores it.
struct StoredMessage {
    let rowID: Int64
    let guid: String
    let sender: String
    let date: Date
    let text: String?
    let attributedBody: Data?
}

/// ~/Library/Messages/chat.db, opened read-only.
final class ChatDatabase {
    private var db: OpaquePointer?

    init(url: URL) throws {
        // Messages keeps the database in WAL mode; a read-only connection still sees the latest messages
        guard sqlite3_open_v2(url.path, &db, SQLITE_OPEN_READONLY, nil) == SQLITE_OK else {
            let message = db.map { String(cString: sqlite3_errmsg($0)) } ?? "unknown error"
            sqlite3_close(db)
            db = nil
            throw Failure("Couldn't open \(Paths.display(url)): \(message)")
        }

        sqlite3_busy_timeout(db, 5000)
        sqlite3_exec(db, "PRAGMA query_only = ON", nil, nil, nil)
    }

    deinit {
        sqlite3_close(db)
    }

    // message.date counts from 2001-01-01, in nanoseconds since macOS 10.13 and in seconds before
    private static let seconds = "(CASE WHEN m.date > 1000000000000 THEN m.date / 1000000000 ELSE m.date END)"

    func newestRowID() throws -> Int64 {
        var newest: Int64 = 0
        try query("SELECT COALESCE(MAX(ROWID), 0) FROM message") { statement in
            newest = sqlite3_column_int64(statement, 0)
        }
        return newest
    }

    /// Received messages after a ROWID, from a date on, oldest first.
    func incoming(after rowID: Int64, since: Date, limit: Int) throws -> [StoredMessage] {
        let sql = """
            SELECT m.ROWID, m.guid, m.date, m.text, m.attributedBody, h.id
            FROM message m
            JOIN handle h ON h.ROWID = m.handle_id
            WHERE m.ROWID > ? AND m.is_from_me = 0 AND \(Self.seconds) >= ?
            ORDER BY m.ROWID
            LIMIT ?
            """

        var messages: [StoredMessage] = []

        try query(sql, bind: { statement in
            sqlite3_bind_int64(statement, 1, rowID)
            sqlite3_bind_int64(statement, 2, Int64(since.timeIntervalSinceReferenceDate.rounded(.down)))
            sqlite3_bind_int64(statement, 3, Int64(limit))
        }) { statement in
            messages.append(StoredMessage(
                rowID: sqlite3_column_int64(statement, 0),
                guid: Self.text(statement, 1) ?? "",
                sender: Self.text(statement, 5) ?? "",
                date: MessagesTime.date(from: sqlite3_column_int64(statement, 2)),
                text: Self.text(statement, 3),
                attributedBody: Self.blob(statement, 4)
            ))
        }

        return messages
    }

    /// Who sent you messages: sender id, how many, and the latest.
    func senders() throws -> [(sender: String, count: Int, last: Date)] {
        let sql = """
            SELECT h.id, COUNT(*), MAX(\(Self.seconds))
            FROM message m
            JOIN handle h ON h.ROWID = m.handle_id
            WHERE m.is_from_me = 0
            GROUP BY h.id
            ORDER BY 3 DESC
            """

        var senders: [(sender: String, count: Int, last: Date)] = []

        try query(sql) { statement in
            senders.append((
                sender: Self.text(statement, 0) ?? "",
                count: Int(sqlite3_column_int64(statement, 1)),
                last: Date(timeIntervalSinceReferenceDate: TimeInterval(sqlite3_column_int64(statement, 2)))
            ))
        }

        return senders
    }

    private func query(_ sql: String, bind: (OpaquePointer?) -> Void = { _ in }, row: (OpaquePointer?) -> Void) throws {
        var statement: OpaquePointer?

        guard sqlite3_prepare_v2(db, sql, -1, &statement, nil) == SQLITE_OK else {
            throw Failure("Couldn't read Messages: \(String(cString: sqlite3_errmsg(db)))")
        }

        defer { sqlite3_finalize(statement) }

        bind(statement)

        while true {
            let result = sqlite3_step(statement)

            if result == SQLITE_ROW {
                row(statement)
            } else if result == SQLITE_DONE {
                return
            } else {
                throw Failure("Couldn't read Messages: \(String(cString: sqlite3_errmsg(db)))")
            }
        }
    }

    private static func text(_ statement: OpaquePointer?, _ column: Int32) -> String? {
        guard sqlite3_column_type(statement, column) != SQLITE_NULL, let pointer = sqlite3_column_text(statement, column) else {
            return nil
        }

        return String(cString: pointer)
    }

    private static func blob(_ statement: OpaquePointer?, _ column: Int32) -> Data? {
        guard sqlite3_column_type(statement, column) != SQLITE_NULL, let pointer = sqlite3_column_blob(statement, column) else {
            return nil
        }

        return Data(bytes: pointer, count: Int(sqlite3_column_bytes(statement, column)))
    }
}

enum MessagesTime {
    /// message.date: nanoseconds since 2001-01-01 on macOS 10.13 and later, seconds before.
    static func date(from value: Int64) -> Date {
        let seconds = abs(value) > 1_000_000_000_000 ? Double(value) / 1_000_000_000 : Double(value)
        return Date(timeIntervalSinceReferenceDate: seconds)
    }
}

enum MessageText {
    /// The text of a message: `message.text`, or on recent macOS (where that's often empty)
    /// the string inside `message.attributedBody`.
    static func from(text: String?, attributedBody: Data?) -> String? {
        if let text, !text.trimmingCharacters(in: .whitespacesAndNewlines).isEmpty {
            return text
        }

        guard let attributedBody, !attributedBody.isEmpty else {
            return nil
        }

        return HSSStringFromTypedStream(attributedBody) ?? fromTypedStreamBytes(attributedBody)
    }

    /// Fallback when Foundation can't unarchive the body: the first NSString's bytes, read directly.
    /// In the typedstream they follow the class name, a '+' marker and a length
    /// (one byte, or 0x81 and two bytes, or 0x82 and four bytes, little-endian).
    static func fromTypedStreamBytes(_ data: Data) -> String? {
        let bytes = [UInt8](data)
        let marker = Array("NSString".utf8)

        guard let start = firstIndex(of: marker, in: bytes) else {
            return nil
        }

        var index = start + marker.count
        let searchEnd = min(index + 16, bytes.count)

        guard let plus = bytes[index..<searchEnd].firstIndex(of: 0x2B) else {
            return nil
        }

        index = plus + 1

        guard index < bytes.count else {
            return nil
        }

        var length = 0

        switch bytes[index] {
        case 0x81:
            guard index + 2 < bytes.count else { return nil }
            length = Int(bytes[index + 1]) | Int(bytes[index + 2]) << 8
            index += 3
        case 0x82:
            guard index + 4 < bytes.count else { return nil }
            length = (0..<4).reduce(0) { $0 | Int(bytes[index + 1 + $1]) << (8 * $1) }
            index += 5
        case let byte where byte < 0x80:
            length = Int(byte)
            index += 1
        default:
            return nil
        }

        guard length > 0, index + length <= bytes.count else {
            return nil
        }

        return String(bytes: bytes[index..<(index + length)], encoding: .utf8)
    }

    private static func firstIndex(of needle: [UInt8], in haystack: [UInt8]) -> Int? {
        guard !needle.isEmpty, haystack.count >= needle.count else {
            return nil
        }

        for index in 0...(haystack.count - needle.count) where haystack[index] == needle[0] {
            if Array(haystack[index..<(index + needle.count)]) == needle {
                return index
            }
        }

        return nil
    }
}
