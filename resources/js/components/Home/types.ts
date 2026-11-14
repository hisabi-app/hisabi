import type { BudgetStatus } from '@/Utils/budgets';

export type Stage = 'new' | 'early' | 'full';

export interface CategorySummary {
    id: number;
    name: string;
    color: string | null;
    icon: string | null;
}

export interface UncategorizedBrand {
    id: number;
    name: string;
    transactions: number;
    total: number;
}

export interface AttentionBudget {
    id: number;
    name: string;
    spent: number;
    amount: number;
    status: BudgetStatus;
}

export interface Budget {
    id: number;
    name: string;
    amount: number | string;
    reoccurrence: string;
    total_transactions_amount: number | string;
    elapsed_days_percentage: number;
    start_at_date: string;
    end_at_date: string;
    status: BudgetStatus;
    is_saving: boolean;
    color: string | null;
    icon: string | null;
}

export interface Transaction {
    id: number;
    amount: number | string;
    note: string | null;
    created_at: string;
    brand: {
        id: number;
        name: string;
        category: (CategorySummary & { type: string }) | null;
    };
}

export type Insight =
    | { kind: 'top_brand'; brand: { id: number; name: string }; category: CategorySummary | null; total: number; count: number }
    | { kind: 'category_up'; category: CategorySummary | null; difference: number; total: number }
    | { kind: 'saved'; category: CategorySummary | null; total: number; last_at: string | null };

export interface HomeData {
    stage: Stage;
    needs_you: {
        brands: { count: number; items: UncategorizedBrand[] };
        budgets: AttentionBudget[];
        unparsed_sms: number;
    };
    month: {
        spent: number;
        spent_last_month_to_date: number;
        has_last_month: boolean;
        started_at: string | null;
        daily: number[];
        last_month_daily: number[];
    };
    insights: Insight[];
    budgets: Budget[];
    recent: Transaction[];
    suggestion: { category: CategorySummary | null; spent: number } | null;
}

export type Detail = { kind: 'budget'; id: number } | { kind: 'brands' } | null;
