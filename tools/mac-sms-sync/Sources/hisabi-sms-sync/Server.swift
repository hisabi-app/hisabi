import Foundation

struct OutgoingMessage: Encodable {
    let guid: String
    let sender: String
    let received_at: String
    let text: String
}

struct SyncResult: Decodable {
    let guid: String
    /// "created" or "duplicate"
    let status: String
    let transaction_id: Int?
}

enum ServerError: Error, CustomStringConvertible {
    case unauthorized
    case forbidden
    case status(Int)
    case unreadableAnswer
    case unreachable(String)

    var description: String {
        switch self {
        case .unauthorized:
            return "The server didn't accept the token (401). Create a new one with php artisan hisabi:sms-sync-token and run hisabi-sms-sync set-token."
        case .forbidden:
            return "The token can't send SMS (403). Create one with php artisan hisabi:sms-sync-token."
        case .status(let code):
            return "The server answered \(code); will try again next run."
        case .unreadableAnswer:
            return "The server's answer wasn't what this tool expects (is \"server\" your Hisabi address?); will try again next run."
        case .unreachable(let reason):
            return "Couldn't reach the server (\(reason)); will try again next run."
        }
    }
}

struct Server {
    static let batchSize = 50

    let endpoint: URL
    let token: String

    /// Send a batch; every message comes back as created or duplicate, or this throws.
    func send(_ messages: [OutgoingMessage]) throws -> [SyncResult] {
        let (data, status) = try post(JSONEncoder().encode(["messages": messages]))

        switch status {
        case 200:
            break
        case 401:
            throw ServerError.unauthorized
        case 403:
            throw ServerError.forbidden
        default:
            // Never log the body: with debug on, an error page could echo the messages back
            throw ServerError.status(status)
        }

        struct Answer: Decodable { let data: [SyncResult] }

        guard let answer = try? JSONDecoder().decode(Answer.self, from: data) else {
            throw ServerError.unreadableAnswer
        }

        let confirmed = Dictionary(answer.data.map { ($0.guid, $0.status) }, uniquingKeysWith: { first, _ in first })

        guard messages.allSatisfy({ ["created", "duplicate"].contains(confirmed[$0.guid]) }) else {
            throw ServerError.unreadableAnswer
        }

        return answer.data
    }

    /// An empty batch is refused (422) only once the token has been accepted.
    func checkToken() -> String {
        do {
            let (_, status) = try post(Data(#"{"messages":[]}"#.utf8))

            switch status {
            case 422:
                return "accepts the token"
            case 401:
                return "doesn't accept the token (401)"
            case 403:
                return "the token can't send SMS (403)"
            default:
                return "answered \(status) (is \"server\" your Hisabi address?)"
            }
        } catch {
            return "\(error)"
        }
    }

    private func post(_ body: Data) throws -> (Data, Int) {
        var request = URLRequest(url: endpoint, cachePolicy: .reloadIgnoringLocalCacheData, timeoutInterval: 30)
        request.httpMethod = "POST"
        request.httpBody = body
        request.setValue("application/json", forHTTPHeaderField: "Content-Type")
        request.setValue("application/json", forHTTPHeaderField: "Accept")
        request.setValue("Bearer \(token)", forHTTPHeaderField: "Authorization")
        request.setValue("hisabi-sms-sync", forHTTPHeaderField: "User-Agent")

        final class Box: @unchecked Sendable {
            var result: Result<(Data, Int), Error> = .failure(ServerError.unreadableAnswer)
        }

        let box = Box()
        let done = DispatchSemaphore(value: 0)

        URLSession.shared.dataTask(with: request) { data, response, error in
            if let error {
                box.result = .failure(ServerError.unreachable(error.localizedDescription))
            } else if let response = response as? HTTPURLResponse {
                box.result = .success((data ?? Data(), response.statusCode))
            }
            done.signal()
        }.resume()

        done.wait()

        return try box.result.get()
    }
}
