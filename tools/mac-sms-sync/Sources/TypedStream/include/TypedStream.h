#import <Foundation/Foundation.h>

NS_ASSUME_NONNULL_BEGIN

/// The text of an NSArchiver typedstream holding an NSAttributedString or NSString
/// (Messages keeps message bodies in `message.attributedBody` this way), or nil if it can't be read.
NSString *_Nullable HSSStringFromTypedStream(NSData *data);

/// A typedstream of an attributed string, as Messages stores one (for the self-test).
NSData *HSSTypedStreamFromString(NSString *string);

NS_ASSUME_NONNULL_END
