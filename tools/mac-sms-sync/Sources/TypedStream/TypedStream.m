#import "TypedStream.h"

// NSUnarchiver is deprecated but it's the only Foundation reader for typedstreams
#pragma clang diagnostic ignored "-Wdeprecated-declarations"

NSString *HSSStringFromTypedStream(NSData *data)
{
    @try {
        id object = [NSUnarchiver unarchiveObjectWithData:data];

        if ([object isKindOfClass:[NSAttributedString class]]) {
            return [(NSAttributedString *)object string];
        }

        if ([object isKindOfClass:[NSString class]]) {
            return (NSString *)object;
        }
    } @catch (NSException *exception) {
        // Unreadable: the caller falls back to reading the string's bytes directly
    }

    return nil;
}

NSData *HSSTypedStreamFromString(NSString *string)
{
    NSAttributedString *attributed = [[NSAttributedString alloc] initWithString:string];
    NSData *data = [NSArchiver archivedDataWithRootObject:attributed];
#if !__has_feature(objc_arc)
    [attributed release];
#endif
    return data;
}
