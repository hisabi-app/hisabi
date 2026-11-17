import type { Comparison } from '@/Utils/kiosk';

// A comparison with what it's compared with, e.g. "than this time in October"
export type LabelledComparison = Comparison & { label: string };

export interface BarRow {
    id?: number;
    name: string;
    color: string | null;
    icon: string | null;
    total: number;
}

export interface TransactionRow {
    id: number;
    brand: string | null;
    amount: number;
    date: string;
    color: string | null;
    icon: string | null;
}

// The pieces an answer is built from. The numbers come from the data, never from Claude's wording.
export type AnswerCard =
    | { type: 'amount'; label: string; total: number; comparison: LabelledComparison | null; daily: number[]; previous_daily: number[] }
    | { type: 'bars'; label: string; rows: BarRow[] }
    | { type: 'budgets'; budgets: any[] }
    | { type: 'transactions'; label: string; rows: TransactionRow[] }
    | { type: 'sentence'; text: string };

export interface Answer {
    question: string;
    title: string;
    say: string;
    cards: AnswerCard[];
    conversation_id: string;
    speech_id: string | null;
}

export interface Assistant {
    enabled: boolean;
    voice: boolean;
    suggestions: string[];
}
