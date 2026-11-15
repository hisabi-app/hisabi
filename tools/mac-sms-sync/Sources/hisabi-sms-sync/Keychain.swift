import Foundation
import Security

/// The server token lives in the login Keychain, never in the config file or the log.
enum Keychain {
    static let service = "dev.hisabi.sms-sync"
    static let account = "token"

    private static var item: [String: Any] {
        [
            kSecClass as String: kSecClassGenericPassword,
            kSecAttrService as String: service,
            kSecAttrAccount as String: account,
        ]
    }

    static func save(_ token: String) throws {
        var attributes = item
        attributes[kSecValueData as String] = Data(token.utf8)
        attributes[kSecAttrLabel as String] = "Hisabi SMS sync"

        var status = SecItemAdd(attributes as CFDictionary, nil)

        if status == errSecDuplicateItem {
            status = SecItemUpdate(item as CFDictionary, [kSecValueData as String: Data(token.utf8)] as CFDictionary)
        }

        guard status == errSecSuccess else {
            throw Failure("Couldn't save the token in the Keychain: \(describe(status))")
        }
    }

    static func token() throws -> String {
        #if DEBUG
        if let token = ProcessInfo.processInfo.environment["HISABI_SMS_SYNC_TOKEN"] {
            return token
        }
        #endif

        var query = item
        query[kSecReturnData as String] = true
        query[kSecMatchLimit as String] = kSecMatchLimitOne

        var result: CFTypeRef?
        let status = SecItemCopyMatching(query as CFDictionary, &result)

        guard status == errSecSuccess, let data = result as? Data, let token = String(data: data, encoding: .utf8), !token.isEmpty else {
            if status == errSecItemNotFound {
                throw Failure("No token yet: run hisabi-sms-sync set-token")
            }

            throw Failure("Couldn't read the token from the Keychain: \(describe(status))")
        }

        return token
    }

    /// Whether a token is saved, without reading it (so no Keychain prompt).
    static func hasToken() -> Bool {
        #if DEBUG
        if ProcessInfo.processInfo.environment["HISABI_SMS_SYNC_TOKEN"] != nil {
            return true
        }
        #endif

        var query = item
        query[kSecReturnAttributes as String] = true

        return SecItemCopyMatching(query as CFDictionary, nil) == errSecSuccess
    }

    private static func describe(_ status: OSStatus) -> String {
        (SecCopyErrorMessageString(status, nil) as String?) ?? "error \(status)"
    }
}
