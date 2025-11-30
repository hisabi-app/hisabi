import React, { createContext, useContext, useState, ReactNode } from 'react';

export interface Range {
    key: string;
    name: string;
}

interface RangeContextType {
    selectedRange: string;
    setSelectedRange: (range: string) => void;
    ranges: Range[];
}

const RangeContext = createContext<RangeContextType | undefined>(undefined);

export const defaultRanges: Range[] = [
    { key: 'current-month', name: 'Current Month' },
    { key: 'last-month', name: 'Last Month' },
    { key: 'last-twelve-months', name: 'Last 12 Months' },
    { key: 'current-year', name: 'Current Year' },
    { key: 'last-year', name: 'Last Year' },
    { key: 'all-time', name: 'All Time' },
];

interface RangeProviderProps {
    children: ReactNode;
    initialRange?: string;
}

export function RangeProvider({ children, initialRange = 'current-month' }: RangeProviderProps) {
    const [selectedRange, setSelectedRange] = useState(initialRange);

    return (
        <RangeContext.Provider value={{ selectedRange, setSelectedRange, ranges: defaultRanges }}>
            {children}
        </RangeContext.Provider>
    );
}

export function useRange() {
    const context = useContext(RangeContext);
    if (context === undefined) {
        throw new Error('useRange must be used within a RangeProvider');
    }
    return context;
}
