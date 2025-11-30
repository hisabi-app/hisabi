import { useEffect, useState } from 'react';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { getBrandStats } from '@/Api';
import { formatNumber, getAppCurrency } from '@/Utils';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';

function BrandStats() {
    const [stats, setStats] = useState({
        mostUsedBrand: null,
        highestSpendingBrand: null,
        highestIncomeBrand: null
    });
    const [range, setRange] = useState('current-month');
    const [loading, setLoading] = useState(false);

    useEffect(() => {
        setLoading(true);
        getBrandStats(range)
            .then(({ data }) => {
                setStats({
                    mostUsedBrand: data.mostUsedBrand,
                    highestSpendingBrand: data.highestSpendingBrand,
                    highestIncomeBrand: data.highestIncomeBrand
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

