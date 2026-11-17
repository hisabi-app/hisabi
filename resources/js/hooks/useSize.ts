import { useEffect, useRef, useState } from 'react';

// An element's size and font size in px, kept current as the screen or the cards around it change
export function useSize<T extends HTMLElement>() {
    const ref = useRef<T>(null);
    const [size, setSize] = useState({ width: 0, height: 0, em: 0 });

    useEffect(() => {
        if (!ref.current) return;

        const observer = new ResizeObserver(([entry]) =>
            setSize({ width: entry.contentRect.width, height: entry.contentRect.height, em: parseFloat(getComputedStyle(entry.target).fontSize) }),
        );
        observer.observe(ref.current);

        return () => observer.disconnect();
    }, []);

    return [ref, size] as const;
}
