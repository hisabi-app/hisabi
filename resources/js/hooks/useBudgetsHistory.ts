import { useRef, useState } from 'react';
import { getBudgetsHistory } from '@/Api/budgets';
import type { HistoryRange, HistoryState } from '@/components/Domain/BudgetHistory';

export function useBudgetsHistory() {
    const [state, setState] = useState<HistoryState>({ loading: false, error: false });
    const latestRequest = useRef(0);

    // Without a range, loads the default window once; with one, reloads for that range.
    const load = (range?: HistoryRange) => {
        if (!range && (state.data || state.loading)) return;

        // Only the latest request may update the state
        const request = ++latestRequest.current;
        setState((current) => ({ ...current, loading: true, error: false }));
        getBudgetsHistory(range)
            .then((data) => request === latestRequest.current && setState({ data, loading: false, error: false }))
            .catch(() => request === latestRequest.current && setState((current) => ({ ...current, loading: false, error: true })));
    };

    // Reloads the range currently shown
    const reload = () => state.data && load({ from: state.data.meta.from, to: state.data.meta.to });

    return { state, load, reload };
}
