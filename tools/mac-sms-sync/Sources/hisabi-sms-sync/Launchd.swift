import Foundation

/// macOS gives Full Disk Access to the process responsible for a run. When launchd starts this binary,
/// that's the binary itself; when you start it from Terminal, it's Terminal. So commands that read
/// Messages are handed to launchd when Terminal can't read them.
enum MessagesAccess {
    /// nil when Messages can be read here. Otherwise the exit code of the same command run through launchd.
    static func ensureReadable(arguments: [String]) throws -> Int32? {
        let descriptor = open(Paths.messages.path, O_RDONLY)

        if descriptor >= 0 {
            close(descriptor)
            return nil
        }

        if errno == ENOENT {
            throw Failure("There's no \(Paths.display(Paths.messages)). Is Messages set up on this Mac, with Messages in iCloud on?")
        }

        let interactive = isatty(STDIN_FILENO) != 0

        guard interactive, !OneOffJob.isOneOff else {
            throw Failure("No Full Disk Access to read Messages. Add \(Bundle.main.executablePath ?? "the hisabi-sms-sync binary") in System Settings > Privacy & Security > Full Disk Access (again after reinstalling it).")
        }

        print("Terminal can't read Messages, so launchd runs this (it uses the binary's own Full Disk Access)…\n")
        fflush(stdout)

        return OneOffJob.run(arguments)
    }
}

enum OneOffJob {
    static let label = "dev.hisabi.sms-sync.once"
    private static let exitFileKey = "HISABI_SMS_SYNC_EXIT_FILE"

    static var isOneOff: Bool {
        ProcessInfo.processInfo.environment[exitFileKey] != nil
    }

    /// In a run started by `run(_:)`, tell the waiting command how it went.
    static func finish(_ code: Int32) {
        guard let path = ProcessInfo.processInfo.environment[exitFileKey] else { return }

        fflush(stdout)
        try? String(code).write(toFile: path, atomically: true, encoding: .utf8)
    }

    /// Run this binary with these arguments as a one-off launchd job, show its output, and return its exit code.
    static func run(_ arguments: [String]) -> Int32 {
        guard let executable = Bundle.main.executablePath else {
            print("Couldn't find this binary's path.")
            return 1
        }

        let manager = FileManager.default
        let plist = Paths.support.appendingPathComponent("once.plist")
        let output = Paths.support.appendingPathComponent("once.out")
        let exitFile = Paths.support.appendingPathComponent("once.exit")
        let domain = "gui/\(getuid())"

        func cleanUp() {
            launchctl(["bootout", "\(domain)/\(label)"])
            // The output may hold message text (a dry run): don't leave it behind
            [plist, output, exitFile].forEach { try? manager.removeItem(at: $0) }
        }

        var environment = [exitFileKey: exitFile.path]

        #if DEBUG
        for key in ["HISABI_SMS_SYNC_HOME", "HISABI_SMS_SYNC_TOKEN"] {
            environment[key] = ProcessInfo.processInfo.environment[key]
        }
        #endif

        do {
            try Paths.ensureSupportDirectory()
            cleanUp()

            let job: [String: Any] = [
                "Label": label,
                "ProgramArguments": [executable] + arguments,
                "EnvironmentVariables": environment,
                "StandardOutPath": output.path,
                "StandardErrorPath": output.path,
                "RunAtLoad": true,
                "ProcessType": "Interactive",
            ]

            try PropertyListSerialization.data(fromPropertyList: job, format: .xml, options: 0).write(to: plist)
        } catch {
            print("Couldn't prepare the launchd job: \(error.localizedDescription)")
            return 1
        }

        defer { cleanUp() }

        guard launchctl(["bootstrap", domain, plist.path]) == 0 else {
            print("launchctl couldn't start the job.")
            return 1
        }

        var printed: UInt64 = 0
        var code: Int32?
        let started = Date()

        func printNewOutput() {
            guard let handle = try? FileHandle(forReadingFrom: output) else { return }
            defer { try? handle.close() }

            handle.seek(toFileOffset: printed)
            let data = handle.readDataToEndOfFile()
            printed += UInt64(data.count)
            FileHandle.standardOutput.write(data)
        }

        func readExitCode() -> Int32? {
            (try? String(contentsOf: exitFile, encoding: .utf8)).flatMap { Int32($0.trimmingCharacters(in: .whitespacesAndNewlines)) }
        }

        while Date().timeIntervalSince(started) < 30 * 60 {
            printNewOutput()

            if let exitCode = readExitCode() {
                code = exitCode
                break
            }

            // Stopped without saying how it went (a crash)
            if Date().timeIntervalSince(started) > 3, !isRunning() {
                code = readExitCode()
                break
            }

            usleep(200_000)
        }

        printNewOutput()

        guard let code else {
            print("\nThe run stopped without finishing. If it says nothing above, check that \(executable) has Full Disk Access.")
            return 1
        }

        return code
    }

    private static func isRunning() -> Bool {
        let pipe = Pipe()
        let process = Process()
        process.executableURL = URL(fileURLWithPath: "/bin/launchctl")
        process.arguments = ["list", label]
        process.standardOutput = pipe
        process.standardError = FileHandle.nullDevice

        guard (try? process.run()) != nil else { return false }

        let listing = String(decoding: pipe.fileHandleForReading.readDataToEndOfFile(), as: UTF8.self)
        process.waitUntilExit()

        return listing.contains("\"PID\" =")
    }
}

@discardableResult
func launchctl(_ arguments: [String]) -> Int32 {
    let process = Process()
    process.executableURL = URL(fileURLWithPath: "/bin/launchctl")
    process.arguments = arguments
    process.standardOutput = FileHandle.nullDevice
    process.standardError = FileHandle.nullDevice

    guard (try? process.run()) != nil else { return -1 }

    process.waitUntilExit()

    return process.terminationStatus
}
