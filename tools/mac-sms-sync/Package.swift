// swift-tools-version:5.9
import PackageDescription

let package = Package(
    name: "HisabiSmsSync",
    platforms: [.macOS(.v13)],
    targets: [
        // Objective-C, so a message body Foundation can't unarchive is caught instead of crashing the run
        .target(name: "TypedStream", path: "Sources/TypedStream"),
        .executableTarget(
            name: "hisabi-sms-sync",
            dependencies: ["TypedStream"],
            path: "Sources/hisabi-sms-sync",
            linkerSettings: [.linkedLibrary("sqlite3"), .linkedFramework("Security")]
        ),
    ]
)
