import { useEffect, useMemo, useState } from 'react';
import { CaretDownIcon } from '@phosphor-icons/react';

import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import CirclePackMetric from '@/components/Domain/CirclePackMetric';
import PartitionMetric from '@/components/Domain/PartitionMetric';
import TrendMetric from '@/components/Domain/TrendMetric';
import { getAllBrands, getAllCategories } from '@/Api';
import { cn } from '@/lib/utils';
import { periodDates, type Period } from './period';

// The detailed charts from the old Insights page, kept one tap away
export default function MoreCharts({ period, index }: { period: Period; index: number }) {
    const [open, setOpen] = useState(false);
    const [categories, setCategories] = useState<any[]>([]);
    const [brands, setBrands] = useState<any[]>([]);

    useEffect(() => {
        if (!open || categories.length) return;

        Promise.all([getAllCategories(), getAllBrands()])
            .then(([{ data: categoryData }, { data: brandData }]) => {
                setCategories(categoryData.allCategories);
                setBrands(brandData.allBrands);
            })
            .catch(console.error);
    }, [open, categories.length]);

    // The older charts always need a range; "All time" becomes everything up to today
    const dateRange = useMemo(() => periodDates(period) ?? { from: new Date(2000, 0, 1), to: new Date() }, [period]);
    const categoryRelation = useMemo(() => ({ data: categories, display_using: 'name', foreign_key: 'id' }), [categories]);
    const categoryRelationForBrands = useMemo(() => ({ data: categories, display_using: 'name', foreign_key: 'category_id' }), [categories]);
    const brandRelation = useMemo(() => ({ data: brands, display_using: 'name', foreign_key: 'id' }), [brands]);

    return (
        <Collapsible open={open} onOpenChange={setOpen} className="rise" style={{ '--i': index } as React.CSSProperties}>
            <CollapsibleTrigger className="flex w-full items-center justify-between gap-3 rounded-2xl bg-card px-[18px] py-3.5 text-left shadow-card transition-colors duration-150 hover:bg-secondary/60">
                <span className="text-muted-foreground">
                    <b className="font-semibold text-foreground">More charts</b> · trends by category and brand, daily spending, income sources, the bubble map
                </span>
                <CaretDownIcon size={15} weight="bold" className={cn('shrink-0 text-muted-foreground transition-transform duration-200 ease-out-quint', open && 'rotate-180')} aria-hidden="true" />
            </CollapsibleTrigger>
            <CollapsibleContent>
                {open && (
                    <div className="mt-3.5 grid gap-3.5 md:grid-cols-2">
                        <TrendMetric name="Income over time" metric="totalIncomeTrend" dateRange={dateRange} />
                        <PartitionMetric name="Income sources" metric="incomePerCategory" show_currency dateRange={dateRange} />
                        <TrendMetric name="Trend by category" metric="totalPerCategoryTrend" relation={categoryRelation} dateRange={dateRange} />
                        <TrendMetric name="Daily trend by category" metric="totalPerCategoryDailyTrend" relation={categoryRelation} dateRange={dateRange} defaultToCurrentYear={false} />
                        <PartitionMetric name="Spending by brand" metric="totalPerBrand" relation={categoryRelationForBrands} show_currency dateRange={dateRange} />
                        <TrendMetric name="Trend by brand" metric="totalPerBrandTrend" relation={brandRelation} dateRange={dateRange} />
                        <div className="md:col-span-2">
                            <CirclePackMetric name="Where everything went" metric="financeVisualizationCirclePackMetric" dateRange={dateRange} />
                        </div>
                    </div>
                )}
            </CollapsibleContent>
        </Collapsible>
    );
}
