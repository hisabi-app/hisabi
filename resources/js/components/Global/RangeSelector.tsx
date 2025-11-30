import { useRange } from '@/contexts/RangeContext';

export default function RangeSelector() {
    const { selectedRange, setSelectedRange, ranges } = useRange();

    return (
        <select
            className="min-w-32 h-8 text-xs border-none appearance-none bg-gray-100 pl-2 pr-6 rounded active:outline-none active:shadow-outline focus:outline-none focus:shadow-outline"
            name="range"
            value={selectedRange}
            onChange={(e) => setSelectedRange(e.target.value)}
        >
            {ranges.map(range => (
                <option key={range.key} value={range.key}>
                    {range.name}
                </option>
            ))}
        </select>
    );
}
