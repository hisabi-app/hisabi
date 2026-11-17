import { useEffect, useRef, useState } from 'react';
import { Head } from '@inertiajs/react';
import { ChatCircle, Microphone } from '@phosphor-icons/react';
import { format, formatDistanceToNowStrict, parseISO, subMonths } from 'date-fns';

import MonthChart from '@/components/Home/MonthChart';
import type { Transaction } from '@/components/Home/types';
import AnswerPage, { ThinkingPage } from '@/components/Kiosk/AnswerPage';
import BudgetRingsCard from '@/components/Kiosk/BudgetRingsCard';
import CategoryBarsCard from '@/components/Kiosk/CategoryBarsCard';
import Listening, { type ListeningMode } from '@/components/Kiosk/Listening';
import AmountCard from '@/components/Kiosk/AmountCard';
import type { Answer, Assistant } from '@/components/Kiosk/types';
import { Skeleton } from '@/components/ui/skeleton';
import Tile from '@/components/Wallet/Tile';
import { amount } from '@/components/Wallet/format';
import { askQuestion, getKioskSummary, getSpeech, transcribeQuestion } from '@/Api/kiosk';
import { setAudioSession, useVoiceRecorder } from '@/hooks/useVoiceRecorder';
import { getAppCurrency } from '@/Utils';
import { toNumber } from '@/Utils/budgets';
import { compareWithLastMonth, isNight, KioskBudget, KioskCategory, msUntilDailyReload, toKioskBudgets } from '@/Utils/kiosk';
import { cn } from '@/lib/utils';

const REFRESH_INTERVAL = 60 * 60 * 1000;
const RETRY_INTERVAL = 5 * 60 * 1000;
const CLOCK_INTERVAL = 60 * 1000;
const PAGE_INTERVAL = 20 * 1000;
// After an answer has been read out, or a listening screen nobody uses, the overview comes back by itself
const ANSWER_INTERVAL = 60 * 1000;
const LISTENING_INTERVAL = 30 * 1000;
// Speech comes back soft for a room with a kettle on: its loud and quiet parts are evened out, then all of it raised
const SPEECH_GAIN = 2.8;

// The overview and the categories take turns on the screen
type PageName = 'overview' | 'categories';
const PAGES: PageName[] = ['overview', 'categories'];
const nextPage = (page: PageName): PageName => (page === 'overview' ? 'categories' : 'overview');

// Asking Claude: listening, then looking it up (question is null while it's still being heard), then the answer
type Ask =
    | { stage: 'idle' }
    | { stage: 'listening' }
    | { stage: 'thinking'; question: string | null }
    | { stage: 'answer'; answer: Answer }
    | { stage: 'error'; question: string | null; message: string };

interface Summary {
    spent: number;
    spentLastMonthToDate: number;
    budgets: KioskBudget[];
    daily: number[];
    lastMonthDaily: number[];
    categories: KioskCategory[];
    latest: Transaction | null;
    latestAt: string | null;
    assistant: Assistant;
}

export default function Kiosk() {
    const [summary, setSummary] = useState<Summary | null>(null);
    const [updatedAt, setUpdatedAt] = useState<Date | null>(null);
    const [failed, setFailed] = useState(false);
    const [now, setNow] = useState(() => new Date());
    const [page, setPage] = useState<PageName>('overview');
    const [ask, setAsk] = useState<Ask>({ stage: 'idle' });
    const [heardNothing, setHeardNothing] = useState(false);
    const [speaking, setSpeaking] = useState(false);
    // Taps while asking, so the screen waits while someone's still looking at it
    const [touches, setTouches] = useState(0);
    const lastAttemptAt = useRef(0);
    const asking = ask.stage !== 'idle';
    const askingRef = useRef(asking);
    askingRef.current = asking;
    // One context listens and another speaks: on iOS, sound played where the microphone was heard comes out quieter
    const audio = useRef<{ listen: AudioContext; speak: AudioContext } | null>(null);
    const playing = useRef<AudioBufferSourceNode | null>(null);
    // "Ask again" continues the conversation; Done forgets it
    const conversation = useRef<string | null>(null);
    // Bumped by every new question or Done, so a late reply to an old one is ignored
    const asked = useRef(0);

    useEffect(() => {
        let timer: number | undefined;
        let reloadTimer: number | undefined;
        let cancelled = false;

        const refresh = () => {
            window.clearTimeout(timer);
            lastAttemptAt.current = Date.now();

            getKioskSummary()
                .then((data) => {
                    if (cancelled) return;

                    setSummary({
                        spent: toNumber(data.spent),
                        spentLastMonthToDate: toNumber(data.spent_last_month_to_date),
                        budgets: toKioskBudgets(data.budgets),
                        daily: data.daily ?? [],
                        lastMonthDaily: data.last_month_daily ?? [],
                        categories: data.categories ?? [],
                        latest: data.latest ?? null,
                        latestAt: data.latest_at ?? null,
                        assistant: data.assistant ?? { enabled: false, voice: false, suggestions: [] },
                    });
                    setUpdatedAt(new Date());
                    setFailed(false);
                    timer = window.setTimeout(refresh, REFRESH_INTERVAL);
                })
                .catch((error) => {
                    if (cancelled) return;

                    // The session expired: reloading lands on the login page
                    if (error.status === 401) {
                        window.location.reload();
                        return;
                    }

                    setFailed(true);
                    timer = window.setTimeout(refresh, RETRY_INTERVAL);
                });
        };

        // Timers can pause while the screen is off, so catch up as soon as it's back on
        const onVisibilityChange = () => {
            if (document.visibilityState === 'visible' && Date.now() - lastAttemptAt.current >= REFRESH_INTERVAL) refresh();
        };

        // The daily reload waits for whoever is asking something
        const reload = () => {
            if (askingRef.current) reloadTimer = window.setTimeout(reload, 60 * 1000);
            else window.location.reload();
        };

        refresh();
        document.addEventListener('visibilitychange', onVisibilityChange);
        reloadTimer = window.setTimeout(reload, msUntilDailyReload(new Date()));
        // Keeps the date, "2 hours ago" and day/night current between refreshes
        const clock = window.setInterval(() => setNow(new Date()), CLOCK_INTERVAL);

        return () => {
            cancelled = true;
            window.clearTimeout(timer);
            window.clearTimeout(reloadTimer);
            window.clearInterval(clock);
            document.removeEventListener('visibilitychange', onVisibilityChange);
        };
    }, []);

    const night = isNight(now);
    const hasBudgets = (summary?.budgets.length ?? 0) > 0;
    // Until something is spent this month there's no categories page, and the overview stays up
    const rotates = (summary?.categories.length ?? 0) > 0;
    const currentPage = rotates ? page : 'overview';
    const assistant = summary?.assistant;
    const canAsk = !!assistant?.enabled && (assistant.voice || assistant.suggestions.length > 0);

    // Each page stays up for 20 seconds; a tap turns the page and starts the wait again. Asking holds the page.
    useEffect(() => {
        if (!rotates || asking) return;

        const timer = window.setTimeout(() => setPage(nextPage), PAGE_INTERVAL);

        return () => window.clearTimeout(timer);
    }, [page, rotates, asking]);

    const recorder = useVoiceRecorder({
        onRecorded: (recording) => hear(recording),
        onNoSpeech: () => setHeardNothing(true),
    });

    // A screen left alone (an answer read out, listening that heard nothing) goes back to the overview
    useEffect(() => {
        const waiting =
            ask.stage === 'answer' ||
            ask.stage === 'error' ||
            (ask.stage === 'listening' && recorder.state !== 'listening' && recorder.state !== 'starting');

        if (!waiting || speaking) return;

        const timer = window.setTimeout(done, ask.stage === 'listening' ? LISTENING_INTERVAL : ANSWER_INTERVAL);

        return () => window.clearTimeout(timer);
    }, [ask, speaking, recorder.state, touches]);

    // iOS only plays sound started from a tap, and suspends it again after the screen sleeps, so every tap wakes it
    const wakeAudio = () => {
        audio.current ??= { listen: new AudioContext(), speak: new AudioContext() };
        void audio.current.listen.resume();
        void audio.current.speak.resume();

        return audio.current.listen;
    };

    const stopSpeaking = () => {
        playing.current?.stop();
        playing.current = null;
        setSpeaking(false);
    };

    const listen = () => {
        const context = wakeAudio();
        asked.current++;
        stopSpeaking();
        setHeardNothing(false);
        setAsk({ stage: 'listening' });

        if (assistant?.voice) void recorder.start(context);
    };

    const fail = (error: { status?: number; message?: string }, question: string | null) => {
        // The session expired: reloading lands on the login page
        if (error.status === 401) {
            window.location.reload();
            return;
        }

        setAsk({
            stage: 'error',
            question,
            message: error.status ? (error.message ?? 'Something went wrong.') : 'Couldn’t reach Hisabi. Try again in a moment.',
        });
    };

    const hear = async (recording: Blob) => {
        const id = ++asked.current;
        setAsk({ stage: 'thinking', question: null });

        try {
            const question = await transcribeQuestion(recording);
            if (id === asked.current) await answer(question, id);
        } catch (error: any) {
            if (id !== asked.current) return;

            // Nothing in it worth answering: back to listening, ready for another go
            if (error.status === 422) {
                setHeardNothing(true);
                setAsk({ stage: 'listening' });
            } else {
                fail(error, null);
            }
        }
    };

    const answer = async (question: string, id = ++asked.current) => {
        recorder.cancel();
        setAsk({ stage: 'thinking', question });

        try {
            const reply: Answer = await askQuestion({ question, conversationId: conversation.current });
            if (id !== asked.current) return;

            conversation.current = reply.conversation_id;
            setAsk({ stage: 'answer', answer: reply });

            // Quiet at night, when the kiosk goes dark
            if (reply.speech_id && assistant?.voice && !isNight(new Date())) void speak(reply.speech_id, id);
        } catch (error: any) {
            if (id === asked.current) fail(error, question);
        }
    };

    const speak = async (speechId: string, id: number) => {
        const context = audio.current?.speak;
        if (!context) return;

        try {
            const buffer = await context.decodeAudioData(await getSpeech(speechId));
            if (id !== asked.current) return;

            setAudioSession('playback');
            await context.resume();

            const source = context.createBufferSource();
            source.buffer = buffer;
            playLouder(source, context);
            source.onended = () => {
                if (playing.current !== source) return;
                playing.current = null;
                setSpeaking(false);
            };
            playing.current = source;
            setSpeaking(true);
            source.start();
        } catch {
            // The answer is on the screen either way
        }
    };

    const askSuggestion = (question: string) => {
        wakeAudio();
        void answer(question);
    };

    const done = () => {
        asked.current++;
        recorder.cancel();
        stopSpeaking();
        conversation.current = null;
        setAsk({ stage: 'idle' });
    };

    const listeningMode: ListeningMode = !assistant?.voice
        ? 'tap-only'
        : recorder.state === 'listening'
          ? 'listening'
          : recorder.state === 'unavailable' || recorder.state === 'denied'
            ? recorder.state
            : heardNothing
              ? 'no-speech'
              : 'starting';

    return (
        <div className={cn(night && 'dark')}>
            <Head title="Kiosk" />
            <main
                className="flex h-svh flex-col gap-[1.1em] overflow-hidden bg-background px-[2.1em] pt-[1.9em] pb-[1.7em] leading-tight text-foreground transition-colors duration-1000"
                style={{ fontSize: 'min(1.45vw, 2.35vh)' }}
                onClick={asking ? () => setTouches((count) => count + 1) : rotates ? () => setPage(nextPage) : undefined}
            >
                {summary && updatedAt ? (
                    <>
                        <header className="flex items-end justify-between gap-[1em]">
                            {ask.stage === 'thinking' || ask.stage === 'answer' || ask.stage === 'error' ? (
                                <AskHeader ask={ask} />
                            ) : (
                                <>
                                    <div>
                                        <h1 className="text-[2.5em] leading-none font-bold tracking-[-0.03em]">{format(now, 'MMMM')}</h1>
                                        <p className="mt-[0.35em] text-[1.05em] text-muted-foreground">{format(now, 'EEEE d MMMM')}</p>
                                    </div>
                                    <p className="flex items-center gap-[0.5em] text-[0.95em] text-muted-foreground">
                                        <span className={cn('size-[0.5em] rounded-full', failed ? 'bg-warning-bar' : 'bg-success')} />
                                        {failed
                                            ? `Couldn’t refresh · data from ${format(updatedAt, 'HH:mm')}`
                                            : `Updated ${format(updatedAt, 'HH:mm')}`}
                                    </p>
                                </>
                            )}
                        </header>

                        <div className="grid min-h-0 flex-1 grid-rows-[minmax(0,1fr)]">
                            <KioskPage
                                shown={!asking && currentPage === 'overview'}
                                className={cn('grid grid-rows-[minmax(0,1fr)] gap-[1.1em]', hasBudgets && 'grid-cols-[1.15fr_1fr]')}
                            >
                                <AmountCard
                                    label="Spent so far"
                                    total={summary.spent}
                                    comparison={monthComparison(summary)}
                                    chart={
                                        summary.daily.length > 2
                                            ? (height) => (
                                                  <MonthChart
                                                      daily={summary.daily}
                                                      lastMonth={summary.spentLastMonthToDate > 0 ? summary.lastMonthDaily : []}
                                                      label={`Spending so far in ${format(now, 'MMMM')} against ${format(subMonths(now, 1), 'MMMM')}`}
                                                      height={height}
                                                  />
                                              )
                                            : undefined
                                    }
                                />
                                {hasBudgets && <BudgetRingsCard budgets={summary.budgets} />}
                            </KioskPage>
                            {rotates && (
                                <KioskPage shown={!asking && currentPage === 'categories'} className="flex flex-col">
                                    <CategoryBarsCard label="By category" rows={summary.categories} />
                                </KioskPage>
                            )}
                            <KioskPage shown={asking}>
                                {ask.stage === 'listening' && (
                                    <Listening
                                        mode={listeningMode}
                                        level={recorder.level}
                                        suggestions={assistant?.suggestions ?? []}
                                        onMic={() => (recorder.state === 'listening' ? recorder.stop() : listen())}
                                        onSuggestion={askSuggestion}
                                    />
                                )}
                                {ask.stage === 'thinking' && <ThinkingPage />}
                                {ask.stage === 'answer' && (
                                    <AnswerPage key={ask.answer.conversation_id + ask.answer.question} cards={ask.answer.cards} />
                                )}
                                {ask.stage === 'error' && <AnswerPage cards={[{ type: 'sentence', text: ask.message }]} />}
                            </KioskPage>
                        </div>

                        <footer className="flex items-center justify-between gap-[1em]">
                            {asking ? (
                                <>
                                    <p className="text-[1.05em] text-muted-foreground">
                                        {ask.stage !== 'listening' && 'Claude · from your transactions'}
                                    </p>
                                    <span className="flex items-center gap-[0.8em]">
                                        <button
                                            type="button"
                                            onClick={done}
                                            className="rounded-full bg-card px-[1.25em] py-[0.75em] text-[1.05em] font-semibold shadow-card active:bg-muted"
                                        >
                                            {ask.stage === 'answer' || ask.stage === 'error' ? 'Done' : 'Cancel'}
                                        </button>
                                        {(ask.stage === 'answer' || ask.stage === 'error') && (
                                            <AskButton label="Ask again" voice={!!assistant?.voice} onClick={listen} />
                                        )}
                                    </span>
                                </>
                            ) : (
                                <>
                                    {summary.latest && summary.latestAt ? (
                                        <Latest key={summary.latest.id} transaction={summary.latest} at={summary.latestAt} now={now} />
                                    ) : (
                                        <p className="text-[1.05em] text-muted-foreground">Waiting for this month’s first transaction</p>
                                    )}
                                    <span className="flex shrink-0 items-center gap-[1.3em]">
                                        {rotates && <PageDots current={currentPage} />}
                                        {canAsk && <AskButton label="Ask" voice={!!assistant?.voice} onClick={listen} />}
                                    </span>
                                </>
                            )}
                        </footer>
                    </>
                ) : (
                    <p className="m-auto text-[1.6em] text-muted-foreground">
                        {failed ? 'Couldn’t reach Hisabi, trying again in a few minutes' : 'Loading…'}
                    </p>
                )}
            </main>
        </div>
    );
}

// Plays speech through a compressor that evens it out (so raising it doesn't clip its peaks), then a gain that raises it
function playLouder(source: AudioNode, context: AudioContext) {
    const compressor = context.createDynamicsCompressor();
    compressor.threshold.value = -24;
    compressor.knee.value = 12;
    compressor.ratio.value = 4;
    compressor.attack.value = 0.003;
    compressor.release.value = 0.25;

    const gain = context.createGain();
    gain.gain.value = SPEECH_GAIN;

    source.connect(compressor).connect(gain).connect(context.destination);
}

function monthComparison(summary: Summary) {
    const comparison = compareWithLastMonth(summary.spent, summary.spentLastMonthToDate);

    return comparison && { ...comparison, label: 'than this time last month' };
}

// While asking: what's being answered, under a short title (a shimmer until there is one)
function AskHeader({ ask }: { ask: Extract<Ask, { stage: 'thinking' | 'answer' | 'error' }> }) {
    const question = ask.stage === 'answer' ? ask.answer.question : ask.question;

    return (
        <div className="min-w-0" style={{ animation: 'fade-in 300ms var(--ease-out-quint)' }}>
            {ask.stage === 'thinking' ? (
                <Skeleton className="h-[2.5em] w-[9em] rounded-[0.6em]" />
            ) : (
                <h1 className="truncate text-[2.5em] leading-none font-bold tracking-[-0.03em]">
                    {ask.stage === 'answer' ? ask.answer.title : 'Couldn’t answer'}
                </h1>
            )}
            <p className="mt-[0.35em] truncate text-[1.05em] text-muted-foreground">{question ? `“${question}”` : 'One moment…'}</p>
        </div>
    );
}

// The ink pill that starts a question; it doesn't turn the page
function AskButton({ label, voice, onClick }: { label: string; voice: boolean; onClick: () => void }) {
    const Icon = voice ? Microphone : ChatCircle;

    return (
        <button
            type="button"
            onClick={(event) => {
                event.stopPropagation();
                onClick();
            }}
            className="flex items-center gap-[0.45em] rounded-full bg-foreground px-[1.25em] py-[0.75em] text-[1.05em] font-semibold whitespace-nowrap text-background active:opacity-80"
        >
            <Icon size="1.15em" weight="bold" aria-hidden="true" />
            {label}
        </button>
    );
}

// A page out of turn fades out but stays laid out, so turning back to it is only a fade and nothing draws in again
function KioskPage({ shown, className, children }: { shown: boolean; className?: string; children: React.ReactNode }) {
    return (
        <div
            aria-hidden={!shown}
            className={cn(
                'col-start-1 row-start-1 min-h-0 transition-opacity duration-700 ease-out-quint motion-reduce:transition-none',
                !shown && 'pointer-events-none opacity-0',
                className,
            )}
        >
            {children}
        </div>
    );
}

// Which page is up, as the longer dot
function PageDots({ current }: { current: PageName }) {
    return (
        <span aria-hidden="true" className="flex shrink-0 items-center gap-[0.45em]">
            {PAGES.map((page) => (
                <i
                    key={page}
                    className={cn(
                        'h-[0.5em] rounded-full transition-[width,background-color] duration-700 ease-out-quint motion-reduce:transition-none',
                        page === current ? 'w-[1.5em] bg-foreground' : 'w-[0.5em] bg-muted-foreground/40',
                    )}
                />
            ))}
        </span>
    );
}

function Latest({ transaction, at, now }: { transaction: Transaction; at: string; now: Date }) {
    const category = transaction.brand.category;
    const when = parseISO(at);
    const minutes = (now.getTime() - when.getTime()) / 60000;

    return (
        <p
            className="flex items-center gap-[0.8em] rounded-full bg-card py-[0.5em] pr-[1.2em] pl-[0.5em] text-[1.05em] shadow-card"
            style={{ animation: 'rise 460ms var(--ease-out-quint)' }}
        >
            <Tile color={category?.color} icon={category?.icon} className="size-[2.2em] rounded-full" />
            <b className="font-semibold">{transaction.brand.name}</b>
            <span className="font-rounded tabular">
                {getAppCurrency()} {amount(transaction.amount, 2)}
            </span>
            <span className="text-muted-foreground">{minutes < 2 ? 'Just now' : formatDistanceToNowStrict(when, { addSuffix: true })}</span>
        </p>
    );
}
