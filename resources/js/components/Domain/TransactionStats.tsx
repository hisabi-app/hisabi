import { useEffect, useState } from 'react';
import { Card, CardContent } from '@/components/ui/card';
import { getTransactionStats } from '@/Api';
import { formatNumber, getAppCurrency } from '@/Utils';

function TransactionStats() {
    const [stats, setStats] = useState({ totalIncome: 0, totalExpenses: 0, totalTransactions: 0 });

    useEffect(() => {
        getTransactionStats()
            .then(({ data }) => {
                const income = JSON.parse(data.totalIncome);
                const expenses = JSON.parse(data.totalExpenses);
                const transactionCounts = JSON.parse(data.numberOfTransactions);
                const totalCount = transactionCounts.reduce((sum: number, item: any) => sum + item.value, 0);
                
                setStats({
                    totalIncome: income.value,
                    totalExpenses: expenses.value,
                    totalTransactions: totalCount
                });
            })
            .catch(console.error)
    }, []);

    return (
        <Card className="overflow-hidden p-0">
            <div className="grid grid-cols-3 divide-y md:divide-y-0 md:divide-x">
                <CardContent className="px-6 py-4">
                    <div className="text-sm text-muted-foreground mb-2">Total transactions</div>
                    <div className="font-semibold">{formatNumber(stats.totalTransactions)}</div>
                </CardContent>
                <CardContent className="px-6 py-4">
                    <div className="text-sm text-muted-foreground mb-2">Income</div>
                    <div className="font-semibold">{getAppCurrency()} {formatNumber(stats.totalIncome)}</div>
                </CardContent>
                <CardContent className="px-6 py-4">
                    <div className="text-sm text-muted-foreground mb-2">Expenses</div>
                    <div className="font-semibold">{getAppCurrency()} {formatNumber(stats.totalExpenses)}</div>
                </CardContent>
            </div>
        </Card>
    );
}

export default TransactionStats;

