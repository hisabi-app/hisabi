import { useEffect, useState } from 'react';
import { Card, CardContent } from '@/components/ui/card';
import { getBrandStats } from '@/Api';
import { formatNumber, getAppCurrency } from '@/Utils';
import { DateRange } from 'react-day-picker';

interface BrandStatsProps {
    dateRange: DateRange | undefined;
}

interface BrandStat {
    name: string;
    amount: number;
}

interface MostUsedBrandStat {
    name: string;
    count: number;
}

interface StatsState {
    mostUsedBrand: MostUsedBrandStat | null;
    highestSpendingBrand: BrandStat | null;
    highestIncomeBrand: BrandStat | null;
}

function BrandStats({ dateRange }: BrandStatsProps) {
    const [stats, setStats] = useState<StatsState>({
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
        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
            <Card className="py-0">
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
            </Card>
            <Card className="py-0">
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
            </Card>
            <Card className="py-0">
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
            </Card>
        </div>
    );
}

export default BrandStats;
