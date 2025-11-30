import { useEffect, useRef, useState, useCallback } from 'react';

interface UseInViewOptions {
    threshold?: number;
    rootMargin?: string;
    triggerOnce?: boolean;
}

export function useInView(
    options: UseInViewOptions = {}
): [(node: HTMLDivElement | null) => void, boolean] {
    const { threshold = 0, rootMargin = '100px', triggerOnce = true } = options;
    const [isInView, setIsInView] = useState(false);
    const [node, setNode] = useState<HTMLDivElement | null>(null);
    const observerRef = useRef<IntersectionObserver | null>(null);

    const ref = useCallback((element: HTMLDivElement | null) => {
        setNode(element);
    }, []);

    useEffect(() => {
        // Clean up previous observer
        if (observerRef.current) {
            observerRef.current.disconnect();
            observerRef.current = null;
        }

        if (!node) return;

        // Already triggered and triggerOnce, skip creating observer
        if (triggerOnce && isInView) return;

        observerRef.current = new IntersectionObserver(
            (entries) => {
                const [entry] = entries;
                if (entry.isIntersecting) {
                    setIsInView(true);
                    if (triggerOnce && observerRef.current) {
                        observerRef.current.disconnect();
                        observerRef.current = null;
                    }
                } else if (!triggerOnce) {
                    setIsInView(false);
                }
            },
            { threshold, rootMargin }
        );

        observerRef.current.observe(node);

        return () => {
            if (observerRef.current) {
                observerRef.current.disconnect();
                observerRef.current = null;
            }
        };
    }, [node, threshold, rootMargin, triggerOnce, isInView]);

    return [ref, isInView];
}
