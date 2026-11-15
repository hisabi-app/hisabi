import Foundation

let usage = """
hisabi-sms-sync: sends new bank SMS from Messages on this Mac to your Hisabi server.

  set-token                 Save the token from `php artisan hisabi:sms-sync-token` in the Keychain
  list-senders              Show the sender ids of the messages you received (pick your banks)
  run --since "YYYY-MM-DD HH:MM"
                            First run: send bank SMS received since then (local time)
  run                       Send what arrived since the last run (what the background agent does)
  run --dry-run [--since …] Show what would be sent, without sending anything
  status                    Check the setup
  self-test                 Check the parts that don't need your messages

Config: \(Paths.display(Paths.config))
Log:    \(Paths.display(Paths.log))
"""

func main(_ arguments: [String]) -> Int32 {
    guard let command = arguments.first else {
        print(usage)
        return 1
    }

    let options = Array(arguments.dropFirst())

    do {
        switch command {
        case "run":
            return try SyncCommand.run(options: options, arguments: arguments)
        case "list-senders":
            return try SendersCommand.run(arguments: arguments)
        case "set-token":
            return try TokenCommand.run()
        case "status":
            return StatusCommand.run()
        case "self-test":
            return SelfTest.run()
        case "help", "-h", "--help":
            print(usage)
            return 0
        default:
            print("Unknown command: \(command)\n")
            print(usage)
            return 1
        }
    } catch {
        Log.line("Error: \(error)")
        return 1
    }
}

let code = main(Array(CommandLine.arguments.dropFirst()))
OneOffJob.finish(code)
exit(code)
