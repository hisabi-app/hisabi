import { useEffect, useState } from 'react';

// Loads a metric and reloads it whenever `key` changes (a new period, new data)
export function useMetric<T>(load: () => Promise<T>, key: string) {
    const [state, setState] = useState<{ data: T | null; error: boolean; loading: boolean }>({ data: null, error: false, loading: true });

    useEffect(() => {
        let cancelled = false;
        setState((current) => ({ ...current, loading: true, error: false }));

        load()
            .then((data) => !cancelled && setState({ data, error: false, loading: false }))
            .catch(() => !cancelled && setState((current) => ({ ...current, error: true, loading: false })));

        return () => {
            cancelled = true;
        };
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [key]);

    return state;
}
