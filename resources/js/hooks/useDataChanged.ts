import { useEffect, useRef } from 'react';

const EVENT = 'hisabi:data-changed';

// Tell open pages that transactions changed (added from the sidebar, parsed from an SMS…)
export const notifyDataChanged = () => window.dispatchEvent(new CustomEvent(EVENT));

// Run the callback whenever transactions change somewhere in the app
export function useDataChanged(callback: () => void) {
    const latest = useRef(callback);
    latest.current = callback;

    useEffect(() => {
        const listener = () => latest.current();
        window.addEventListener(EVENT, listener);

        return () => window.removeEventListener(EVENT, listener);
    }, []);
}
