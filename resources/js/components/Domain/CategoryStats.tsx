import { useEffect, useState } from 'react';
import { Card, CardContent } from '@/components/ui/card';
import { getCategoryStats } from '@/Api';
import { formatNumber, getAppCurrency } from '@/Utils';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';

function CategoryStats() {
    const [stats, setStats] = useState({
        mostUsedCategory: null,
        highestSpendingCategory: null,
        highestIncomeCategory: null
    });
    const [range, setRange] = useState('current-month');
    const [loading, setLoading] = useState(false);

    useEffect(() => {
        setLoading(true);
        getCategoryStats(range)
            .then(({ data }) => {
                const categoryStatsData = JSON.parse(data.categoryStats);
                setStats({
                    mostUsedCategory: categoryStatsData.mostUsedCategory,
                    highestSpendingCategory: categoryStatsData.highestSpendingCategory,
                    highestIncomeCategory: categoryStatsData.highestIncomeCategory
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
                    </SelectContent>
                </Select>
            </div>
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

