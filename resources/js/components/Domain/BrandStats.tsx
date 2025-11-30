import { useEffect, useState } from 'react';
import { Card, CardContent } from '@/components/ui/card';
import { getBrandStats } from '@/Api';
import { formatNumber, getAppCurrency } from '@/Utils';
import { DateRange } from 'react-day-picker';

interface BrandStatsProps {
    dateRange: DateRange | undefined;
}

function BrandStats({ dateRange }: BrandStatsProps) {
    const [stats, setStats] = useState({
        mostUsedBrand: null,
        highestSpendingBrand: null,
        highestIncomeBrand: null
    });
    const [loading, setLoading] = useState(false);

    useEffect(() => {
        if (!dateRange?.from || !dateRange?.to) return;

        setLoading(true);
        getBrandStats(dateRange)
            .then(({ data }) => {
                setStats({
                    mostUsedBrand: data.mostUsedBrand,
                    highestSpendingBrand: data.highestSpendingBrand,
                    highestIncomeBrand: data.highestIncomeBrand
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
                            {stats.highestIncomeBrand ? (
                                <div>
                                    <div className='font-semibold'>{stats.highestIncomeBrand.name}</div>
                                    <span className="text-muted-foreground">
                                        {getAppCurrency()} {formatNumber(stats.highestIncomeBrand.amount)}
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
                            {stats.highestSpendingBrand ? (
                                <div>
                                    <div className='font-semibold'>{stats.highestSpendingBrand.name}</div>
                                    <span className="text-muted-foreground">
                                        {getAppCurrency()} {formatNumber(stats.highestSpendingBrand.amount)}
                                    </span>
                                </div>
                            ) : '-'}
                        </div>
                    )}
                </CardContent>
                <CardContent className="px-6 py-4">
                    <div className="text-sm text-muted-foreground mb-2">Most used brand</div>
                    {loading ? (
                        <div className="h-6 w-20 bg-muted animate-pulse rounded"></div>
                    ) : (
                        <div>
                            {stats.mostUsedBrand ? (
                                <div>
                                    <div className='font-semibold'>{stats.mostUsedBrand.name}</div>
                                    <span className="text-muted-foreground">
                                        {formatNumber(stats.mostUsedBrand.count)} transactions
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

export default BrandStats;
