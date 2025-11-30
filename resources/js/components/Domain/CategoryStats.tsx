import { useEffect, useState } from 'react';
import { Card, CardContent } from '@/components/ui/card';
import { getCategoryStats } from '@/Api';
import { formatNumber, getAppCurrency } from '@/Utils';
import { DateRange } from 'react-day-picker';

interface CategoryStatsProps {
    dateRange: DateRange | undefined;
}

function CategoryStats({ dateRange }: CategoryStatsProps) {
    const [stats, setStats] = useState({
        mostUsedCategory: null,
        highestSpendingCategory: null,
        highestIncomeCategory: null
    });
    const [loading, setLoading] = useState(false);

    useEffect(() => {
        if (!dateRange?.from || !dateRange?.to) return;

        setLoading(true);
        getCategoryStats(dateRange)
            .then(({ data }) => {
                setStats({
                    mostUsedCategory: data.mostUsedCategory,
                    highestSpendingCategory: data.highestSpendingCategory,
                    highestIncomeCategory: data.highestIncomeCategory
                });
            })
            .catch(console.error)
            .finally(() => setLoading(false));
    }, [dateRange]);

    return (
        <Card className="overflow-hidden p-0 gap-0">
            <div className="grid grid-cols-3 divide-y md:divide-y-0 md:divide-x">
                <CardContent className="px-6 py-4">
                    <div className="text-sm text-muted-foreground mb-2">Highest income</div>
                    {loading ? (
                        <div className="h-6 w-24 bg-muted animate-pulse rounded"></div>
                    ) : (
                        <div>
                            {stats.highestIncomeCategory ? (
                                <div>
                                    <div className='font-semibold'>{stats.highestIncomeCategory.name}</div>
                                    <span className="text-muted-foreground">
                                        {getAppCurrency()} {formatNumber(stats.highestIncomeCategory.amount)}
                                    </span>
                                </div>
                            ) : '-'}
                        </div>
                    )}
                </CardContent>
                <CardContent className="px-6 py-4">
                    <div className="text-sm text-muted-foreground mb-2">Highest spending</div>
                    {loading ? (
                        <div className="h-6 w-24 bg-muted animate-pulse rounded"></div>
                    ) : (
                        <div>
                            {stats.highestSpendingCategory ? (
                                <div>
                                    <div className='font-semibold'>{stats.highestSpendingCategory.name}</div>
                                    <span className="text-muted-foreground">
                                        {getAppCurrency()} {formatNumber(stats.highestSpendingCategory.amount)}
                                    </span>
                                </div>
                            ) : '-'}
                        </div>
                    )}
                </CardContent>
                <CardContent className="px-6 py-4">
                    <div className="text-sm text-muted-foreground mb-2">Most used category</div>
                    {loading ? (
                        <div className="h-6 w-20 bg-muted animate-pulse rounded"></div>
                    ) : (
                        <div>
                            {stats.mostUsedCategory ? (
                                <div>
                                    <div className='font-semibold'>{stats.mostUsedCategory.name}</div>
                                    <span className="text-muted-foreground">
                                        {formatNumber(stats.mostUsedCategory.count)} transactions
                                    </span>
                                </div>
                            ) : '-'}
                        </div>
                    )}
                </CardContent>
            </div>
        </Card>
    );
}

export default CategoryStats;
