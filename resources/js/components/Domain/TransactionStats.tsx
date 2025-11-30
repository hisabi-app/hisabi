import { useEffect, useState } from 'react';
import { Card, CardContent } from '@/components/ui/card';
import { getTransactionStats } from '@/Api';
import { formatNumber, getAppCurrency } from '@/Utils';
import { DateRange } from 'react-day-picker';

interface TransactionStatsProps {
    dateRange: DateRange | undefined;
}

function TransactionStats({ dateRange }: TransactionStatsProps) {
    const [stats, setStats] = useState({ totalIncome: 0, totalExpenses: 0, totalTransactions: 0 });
    const [loading, setLoading] = useState(false);

    useEffect(() => {
        if (!dateRange?.from || !dateRange?.to) return;

        setLoading(true);
        getTransactionStats(dateRange)
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
    }, [dateRange]);

    return (
        <Card className="overflow-hidden p-0 gap-0">
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
