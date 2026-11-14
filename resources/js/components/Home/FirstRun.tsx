import { useEffect, useState } from 'react';
import { ChatCircleTextIcon } from '@phosphor-icons/react';

import { Button } from '@/components/ui/button';
import { Textarea } from '@/components/ui/textarea';
import PageHeader from '@/components/Wallet/PageHeader';
import Panel from '@/components/Wallet/Panel';
import Tile from '@/components/Wallet/Tile';
import { amount } from '@/components/Wallet/format';
import { createSms } from '@/Api';
import { requestAdd } from '@/hooks/useDataChanged';
import { cn } from '@/lib/utils';
import { smsSegments } from './smsTokens';

interface ParsedSms {
    id: number;
    body: string;
    transaction_id: number | null;
    meta?: { data?: Record<string, string> };
}

const reducedMotion = () => window.matchMedia?.('(prefers-reduced-motion: reduce)').matches;
const STEP_MS = 300;

// Day one: nothing but the SMS box. Paste one and Hisabi shows what it picked out.
export default function FirstRun({ onDone }: { onDone: () => void }) {
    const [text, setText] = useState('');
    const [reading, setReading] = useState(false);
    const [parsed, setParsed] = useState<ParsedSms[] | null>(null);
    const [lit, setLit] = useState(0);
    const [error, setError] = useState(false);

    const totalParts = parsed?.reduce((sum, sms) => sum + smsSegments(sms.body, sms.meta?.data).filter((segment) => segment.label).length, 0) ?? 0;
    const finished = parsed !== null && lit >= totalParts;

    // Light the picked-out parts up one after another
    useEffect(() => {
        if (!parsed || lit >= totalParts) return;

        const timer = window.setTimeout(() => setLit((count) => count + 1), lit === 0 ? 450 : reducedMotion() ? 0 : STEP_MS);

        return () => window.clearTimeout(timer);
    }, [parsed, lit, totalParts]);

    const read = () => {
        if (!text.trim() || reading) return;

        setReading(true);
        setError(false);
        createSms({ sms: text, createdAt: null })
            .then(({ data }) => {
                setParsed(data.createSms);
                setLit(0);
            })
            .catch(() => setError(true))
            .finally(() => setReading(false));
    };

    const found = parsed?.filter((sms) => sms.transaction_id) ?? [];
    const missed = (parsed?.length ?? 0) - found.length;
    let partIndex = 0;

    return (
        <>
            <PageHeader eyebrow="Welcome to Hisabi" title="Let’s add your first transaction" />

            <Panel title="Paste a bank SMS" index={1}>
                <p className="text-muted-foreground">Hisabi picks out the amount, the shop and the date. Everything stays on your own server.</p>

                {parsed === null ? (
                    <Textarea
                        name="sms"
                        value={text}
                        onChange={(event) => setText(event.target.value)}
                        placeholder="Paste an SMS from your bank. Several work too, one per line."
                        className="mt-3.5 min-h-28 rounded-2xl border-0 bg-secondary px-4 py-3.5 text-[13.5px] ring-1 ring-border ring-inset"
                        autoFocus
                    />
                ) : (
                    <div className="mt-3.5 grid gap-2 rounded-2xl bg-secondary px-4 pt-5 pb-3 text-[13.5px] leading-[2.3] ring-1 ring-border ring-inset">
                        {parsed.map((sms) => (
                            <p key={sms.id}>
                                {smsSegments(sms.body, sms.meta?.data).map((segment, i) => {
                                    if (!segment.label) return <span key={i}>{segment.text}</span>;

                                    const isLit = partIndex++ < lit;

                                    return (
                                        <span
                                            key={i}
                                            className={cn(
                                                'relative -mx-px rounded-[5px] px-[3px] py-0.5 transition-[background-color,box-shadow] duration-300 ease-out-quint',
                                                isLit && 'bg-selection shadow-[inset_0_-2px_0_var(--info)]',
                                            )}
                                        >
                                            {isLit && (
                                                <span className="absolute -top-[1.55em] left-[3px] text-[9.5px] leading-none font-bold tracking-[0.04em] whitespace-nowrap text-info uppercase" style={{ animation: 'label-in 300ms var(--ease-out-quint)' }}>
                                                    {segment.label}
                                                </span>
                                            )}
                                            {segment.text}
                                        </span>
                                    );
                                })}
                            </p>
                        ))}
                    </div>
                )}

                <p className={cn('mt-2.5 min-h-[18px] text-[12.5px] font-medium text-muted-foreground', parsed && !finished && 'breathe')} aria-live="polite">
                    {reading && 'Sending…'}
                    {parsed && !finished && 'Reading your SMS…'}
                    {finished && found.length > 0 && `Found ${found.length} ${found.length === 1 ? 'transaction' : 'transactions'}`}
                    {finished && found.length === 0 && 'Hisabi doesn’t know this format yet'}
                    {error && <span className="text-destructive">Couldn’t send the SMS. Try again.</span>}
                </p>

                {finished && found.length > 0 && (
                    <div className="mt-1.5 border-t pt-1.5" style={{ animation: 'rise 460ms var(--ease-out-quint)' }}>
                        {found.map((sms) => (
                            <div key={sms.id} className="grid grid-cols-[36px_minmax(0,1fr)_auto] items-center gap-x-3 py-2.5">
                                <Tile className="row-span-2" label="New brand" />
                                <p className="truncate font-semibold">{sms.meta?.data?.brand}</p>
                                <p className="col-start-2 text-[12.5px] text-muted-foreground">New brand · you’ll pick its category next</p>
                                <p className="col-start-3 row-span-2 row-start-1 text-sm font-semibold font-rounded tabular">{amount(sms.meta?.data?.amount ?? 0, 2)}</p>
                            </div>
                        ))}
                    </div>
                )}

                {finished && missed > 0 && found.length > 0 && (
                    <p className="mt-1 text-[12.5px] text-muted-foreground">
                        {missed} {missed === 1 ? 'message' : 'messages'} couldn’t be read. You can fix {missed === 1 ? 'it' : 'them'} in the SMS parser.
                    </p>
                )}

                <div className="mt-3.5 flex flex-wrap gap-2">
                    {parsed === null && (
                        <>
                            <Button onClick={read} disabled={!text.trim() || reading}>
                                <ChatCircleTextIcon aria-hidden="true" />
                                Read SMS
                            </Button>
                            <Button variant="secondary" onClick={() => requestAdd('manual')}>
                                Add one by hand
                            </Button>
                        </>
                    )}
                    {finished && found.length > 0 && <Button onClick={onDone}>See your month</Button>}
                    {finished && (found.length === 0 || missed > 0) && (
                        <Button variant="secondary" onClick={() => requestAdd('sms')}>
                            Open the SMS parser
                        </Button>
                    )}
                    {finished && (
                        <Button
                            variant="secondary"
                            onClick={() => {
                                setParsed(null);
                                setText('');
                            }}
                        >
                            Paste another
                        </Button>
                    )}
                </div>
            </Panel>

            <p className="rise pt-2 text-center text-[13px] text-muted-foreground" style={{ '--i': 2 } as React.CSSProperties}>
                Your month, budgets and insights will appear here as transactions come in.
            </p>
        </>
    );
}
