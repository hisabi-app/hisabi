import { useEffect, useState } from 'react';
import { Card, CardContent } from '@/components/ui/card';
import { getTransactionStats } from '@/Api';
import { formatNumber, getAppCurrency } from '@/Utils';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';

function TransactionStats() {
    const [stats, setStats] = useState({ totalIncome: 0, totalExpenses: 0, totalTransactions: 0 });
    const [range, setRange] = useState('current-month');
    const [loading, setLoading] = useState(false);

    useEffect(() => {
        setLoading(true);
        getTransactionStats(range)
            .then(({ data }) => {
                const totalCount = data.numberOfTransactions.reduce((sum: number, item: any) => sum + item.value, 0);

                setStats({
                    totalIncome: data.totalIncome.value,
                    totalExpenses: data.totalExpenses.value,
                    totalTransactions: totalCount
                });
            })
            .catch(console.error)
            .finally(() => setLoading(false));
    }, [range]);

    return (
        <Card className="overflow-hidden p-0 relative gap-0">
            <div className='absolute top-2 right-2'>
                <Select value={range} onValueChange={setRange}>
                    <SelectTrigger className='w-auto bg-white'>
                        <SelectValue placeholder="Select period" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="current-month">Current Month</SelectItem>
                        <SelectItem value="last-month">Last Month</SelectItem>
                        <SelectItem value="current-year">Current Year</SelectItem>
                        <SelectItem value="last-year">Last Year</SelectItem>
                        <SelectItem value="all-time">All Time</SelectItem>
                    </SelectContent>
                </Select>
            </div>
            <div className="grid grid-cols-3 divide-y md:divide-y-0 md:divide-x">
                <CardContent className="px-6 py-4">
                    <div className="text-sm text-muted-foreground mb-2">Total transactions</div>
                    {loading ? (
                        <div className="h-6 w-20 bg-muted animate-pulse rounded"></div>
                    ) : (
                        <div className="font-semibold">{formatNumber(stats.totalTransactions)}</div>
                    )}
                </CardContent>
                <CardContent className="px-6 py-4">
                    <div className="text-sm text-muted-foreground mb-2">Income</div>
                    {loading ? (
                        <div className="h-6 w-24 bg-muted animate-pulse rounded"></div>
                    ) : (
                        <div className="font-semibold">{getAppCurrency()} {formatNumber(stats.totalIncome)}</div>
                    )}
                </CardContent>
                <CardContent className="px-6 py-4">
                    <div className="text-sm text-muted-foreground mb-2">Expenses</div>
                    {loading ? (
                        <div className="h-6 w-24 bg-muted animate-pulse rounded"></div>
                    ) : (
                        <div className="font-semibold">{getAppCurrency()} {formatNumber(stats.totalExpenses)}</div>
                    )}
                </CardContent>
            </div>
        </Card>
    );
}

export default TransactionStats;

