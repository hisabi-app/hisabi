import { useEffect, useRef } from 'react';

const CHANGED = 'hisabi:data-changed';
const ADD = 'hisabi:add';

export type AddKind = 'sms' | 'manual';

// Tell open pages that transactions changed (added from the sidebar, parsed from an SMS…)
export const notifyDataChanged = () => window.dispatchEvent(new CustomEvent(CHANGED));

// Ask the layout to open the SMS parser or the "add by hand" form
export const requestAdd = (kind: AddKind) => window.dispatchEvent(new CustomEvent(ADD, { detail: kind }));

function useWindowEvent<T>(name: string, callback: (detail: T) => void) {
    const latest = useRef(callback);
    latest.current = callback;

    useEffect(() => {
        const listener = (event: Event) => latest.current((event as CustomEvent<T>).detail);
        window.addEventListener(name, listener);

        return () => window.removeEventListener(name, listener);
    }, [name]);
}

// Run the callback whenever transactions change somewhere in the app
export function useDataChanged(callback: () => void) {
    useWindowEvent(CHANGED, callback);
}

export function useAddRequests(callback: (kind: AddKind) => void) {
    useWindowEvent(ADD, callback);
}
