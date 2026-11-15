import Foundation

enum Filters {
    /// The second guard after the sender allowlist: one-time codes never leave the Mac.
    static let oneTimeCode = try! NSRegularExpression(
        pattern: #"\b(OTP|one[- ]?time (password|passcode|code|pin)|verification code|security code|authentication code|activation code|passcode)\b"#,
        options: [.caseInsensitive]
    )

    static func isOneTimeCode(_ text: String) -> Bool {
        matches(oneTimeCode, text)
    }

    static func matchesAny(_ expressions: [NSRegularExpression], _ text: String) -> Bool {
        expressions.contains { matches($0, text) }
    }

    /// Bank sender ids such as "ExampleBank": letters (perhaps with digits), not phone numbers or email addresses.
    static func isAlphanumericSender(_ id: String) -> Bool {
        guard !id.isEmpty, id.count <= 20, id.range(of: #"^[A-Za-z0-9 ._&-]+$"#, options: .regularExpression) != nil else {
            return false
        }

        return id.rangeOfCharacter(from: .letters) != nil
    }

    /// Why this server address can't be used, if it can't. The token travels with every request,
    /// so plain http is only accepted for addresses on your own network.
    static func serverProblem(_ address: String) -> String? {
        guard let url = URL(string: address), let scheme = url.scheme?.lowercased(), let host = url.host?.lowercased(), !host.isEmpty else {
            return "\"server\" should be your Hisabi address, like https://hisabi.example.com"
        }

        if scheme == "https" || (scheme == "http" && isOnYourNetwork(host)) {
            return nil
        }

        return "Use an https:// address for \"server\" (plain http only works for addresses on your own network)."
    }

    static func isOnYourNetwork(_ host: String) -> Bool {
        if host == "localhost" || host == "::1" || host.hasSuffix(".local") {
            return true
        }

        let octets = host.split(separator: ".").compactMap { Int($0) }

        guard octets.count == 4 else {
            return false
        }

        return octets[0] == 127 || octets[0] == 10 || (octets[0] == 192 && octets[1] == 168) || (octets[0] == 172 && (16...31).contains(octets[1]))
    }

    private static func matches(_ expression: NSRegularExpression, _ text: String) -> Bool {
        expression.firstMatch(in: text, range: NSRange(text.startIndex..., in: text)) != nil
    }
}
