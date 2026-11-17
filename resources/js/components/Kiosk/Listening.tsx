import { Microphone } from '@phosphor-icons/react';

// tap-only: the questions can only be tapped (no OpenAI key to hear them)
export type ListeningMode = 'starting' | 'listening' | 'no-speech' | 'unavailable' | 'denied' | 'tap-only';

interface ListeningProps {
    mode: ListeningMode;
    // How loud the voice is, 0 to 1
    level: number;
    suggestions: string[];
    onMic: () => void;
    onSuggestion: (question: string) => void;
}

// A ring that follows the voice, with questions to tap underneath
export default function Listening({ mode, level, suggestions, onMic, onSuggestion }: ListeningProps) {
    const withMic = mode === 'starting' || mode === 'listening' || mode === 'no-speech';
    const [title, hint] = {
        starting: ['Listening', 'Pause or tap when you’re done'],
        listening: ['Listening', 'Pause or tap when you’re done'],
        'no-speech': ['Didn’t hear anything', 'Tap the mic to try again'],
        unavailable: ['Tap a question', window.isSecureContext ? 'No microphone found' : 'Speaking needs the kiosk on HTTPS'],
        denied: ['Tap a question', 'Allow the microphone to ask out loud'],
        'tap-only': ['Tap a question', null],
    }[mode];
    const heard = mode === 'listening' ? level : 0;

    return (
        <div className="flex h-full flex-col items-center justify-center gap-[1.6em] text-center">
            {withMic && (
                <button
                    type="button"
                    onClick={onMic}
                    aria-label={mode === 'no-speech' ? 'Try again' : 'Done speaking'}
                    className="relative grid size-[13em] place-items-center"
                >
                    {/* The rings swell with the voice; with reduced motion they hold still */}
                    <span
                        className="absolute inset-0 rounded-full bg-muted transition-[scale] duration-100 ease-out motion-reduce:scale-100!"
                        style={{ scale: String(1 + heard * 0.16) }}
                    />
                    <span
                        className="absolute inset-[1.9em] rounded-full bg-track transition-[scale] duration-100 ease-out motion-reduce:scale-100!"
                        style={{ scale: String(1 + heard * 0.1) }}
                    />
                    <span className="relative grid size-[6em] place-items-center rounded-full bg-foreground text-background">
                        <Microphone size="2.4em" weight="bold" aria-hidden="true" />
                    </span>
                </button>
            )}
            <div style={{ animation: 'fade-in 300ms var(--ease-out-quint)' }} key={mode}>
                <p className="text-[2em] leading-tight font-bold tracking-[-0.02em]">{title}</p>
                {hint && <p className="mt-[0.3em] text-[1.2em] text-muted-foreground">{hint}</p>}
            </div>
            {suggestions.length > 0 && (
                <div className="flex max-w-[52em] flex-col items-center gap-[0.8em]">
                    {withMic && <p className="text-[1em] font-medium text-muted-foreground">Or tap a question</p>}
                    <div className="flex flex-wrap justify-center gap-[0.7em]">
                        {suggestions.map((question, i) => (
                            <button
                                key={question}
                                type="button"
                                onClick={() => onSuggestion(question)}
                                className="rise rounded-full bg-card px-[1.2em] py-[0.75em] text-[1.15em] font-semibold shadow-card active:bg-muted"
                                style={{ '--i': i } as React.CSSProperties}
                            >
                                {question}
                            </button>
                        ))}
                    </div>
                </div>
            )}
        </div>
    );
}
