import Foundation
import TypedStream

/// `self-test`: checks the parts that don't need your messages, with made-up data.
enum SelfTest {
    static func run() -> Int32 {
        var failures: [String] = []
        var checks = 0

        func check(_ condition: Bool, _ name: String) {
            checks += 1
            if !condition { failures.append(name) }
        }

        // attributedBody: archive made-up texts the way Messages does, and read them back both ways
        let texts = [
            "Purchase of AED 12.50 with Debit Card ending 0000 at SAMPLE CAFE, DUBAI. Avl Balance is AED 1,000.00.",
            String(repeating: "Longer than 255 bytes, so the length takes two bytes. ", count: 6),
            "شراء بقيمة 12.50 درهم من متجر تجريبي",
            "Credit Card Purchase\nCard Ending: 0000\nAt: Sample Store, Dubai\nAmount: AED 61.85",
        ]

        for text in texts {
            let archive = HSSTypedStreamFromString(text)
            check(HSSStringFromTypedStream(archive) == text, "Foundation reads an attributedBody (\(text.prefix(20))…)")
            check(MessageText.fromTypedStreamBytes(archive) == text, "the fallback reads an attributedBody (\(text.prefix(20))…)")
            check(MessageText.from(text: nil, attributedBody: archive) == text, "an empty text column falls back to attributedBody")
        }

        let garbage = Data("streamtyped but not really an archive".utf8)
        check(HSSStringFromTypedStream(garbage) == nil, "an unreadable attributedBody is nil, not a crash")
        check(MessageText.from(text: nil, attributedBody: garbage) == nil, "an unreadable attributedBody gives no text")
        check(MessageText.from(text: "Hello", attributedBody: garbage) == "Hello", "the text column comes first")

        // message.date: nanoseconds since 2001 (modern macOS) and seconds (older)
        let moment = ISO8601DateFormatter().date(from: "2026-11-15T09:30:00Z")!
        let seconds = Int64(moment.timeIntervalSinceReferenceDate)
        check(MessagesTime.date(from: seconds * 1_000_000_000) == moment, "message.date in nanoseconds")
        check(MessagesTime.date(from: seconds) == moment, "message.date in seconds")
        check(Dates.iso8601(moment, timeZone: TimeZone(secondsFromGMT: 4 * 3600)!) == "2026-11-15T13:30:00+04:00", "received_at carries the offset")

        // One-time codes stay on the Mac
        check(Filters.isOneTimeCode("Your OTP for the transaction is 123456. Do not share it."), "an OTP is a one-time code")
        check(Filters.isOneTimeCode("123456 is your verification code for Sample Bank"), "a verification code is a one-time code")
        check(Filters.isOneTimeCode("Use one-time password 1234 to log in"), "a one-time password is a one-time code")
        check(!Filters.isOneTimeCode(texts[0]), "a purchase is not a one-time code")
        check(!Filters.isOneTimeCode("Salary of AED 1,000.00 has been credited into your account"), "a salary credit is not a one-time code")

        let skip = [try! NSRegularExpression(pattern: "statement is ready", options: [.caseInsensitive])]
        check(Filters.matchesAny(skip, "Your Statement is Ready to view"), "skip patterns ignore case")

        // Sender ids
        check(Filters.isAlphanumericSender("ExampleBank"), "a bank's sender id is listed")
        check(Filters.isAlphanumericSender("AD-Sample"), "a sender id with a dash is listed")
        check(!Filters.isAlphanumericSender("+971500000000"), "a phone number isn't listed")
        check(!Filters.isAlphanumericSender("12345"), "a short code isn't listed")
        check(!Filters.isAlphanumericSender("someone@example.com"), "an email address isn't listed")

        // The token never travels over plain http to the internet
        check(Filters.serverProblem("https://hisabi.example.com") == nil, "https is accepted")
        check(Filters.serverProblem("http://192.168.1.20:8080") == nil, "http on your own network is accepted")
        check(Filters.serverProblem("http://localhost:8000") == nil, "http on this Mac is accepted")
        check(Filters.serverProblem("http://hisabi.example.com") != nil, "http over the internet is refused")
        check(Filters.serverProblem("hisabi.example.com") != nil, "an address without https:// is refused")

        let config = Config(server: "https://hisabi.example.com/", senders: ["ExampleBank"], skipPatterns: nil)
        check(config.endpoint.absoluteString == "https://hisabi.example.com/api/v1/sms/sync", "the endpoint is built from the server")
        check(config.isAllowed(sender: "examplebank"), "sender ids match whatever the case")
        check(!config.isAllowed(sender: "OtherBank"), "other senders are not allowed")

        let since = Dates.parseSince("2026-11-15 13:30")
        check(since.map { Dates.local($0) } == "2026-11-15 13:30", "--since reads this Mac's time")

        if failures.isEmpty {
            print("Self-test passed (\(checks) checks).")
            return 0
        }

        print("Self-test failed \(failures.count) of \(checks) checks:")
        failures.forEach { print("  - \($0)") }

        return 1
    }
}
