import { useState, useEffect, useMemo } from 'react';
import { Head } from '@inertiajs/react';
import { startOfMonth, endOfMonth } from 'date-fns';
import { DateRange } from 'react-day-picker';

import Authenticated from '@/Layouts/Authenticated';
import NoContent from '@/components/Global/NoContent';
import ValueMetric from '@/components/Domain/ValueMetric';
import TrendMetric from '@/components/Domain/TrendMetric';
import PartitionMetric from '@/components/Domain/PartitionMetric';
import CirclePackMetric from '@/components/Domain/CirclePackMetric';
import SectionDivider from '@/components/Global/SectionDivider';
import Budgets from '@/components/Domain/Budgets';
import RecordTransactionButton from '@/components/Domain/RecordTransactionButton';
import { DatePickerWithRange } from '@/components/ui/date-picker-with-range';
import { getAllCategories } from '@/Api/categories';
import { getAllBrands } from '@/Api/brands';

export default function Dashboard({ auth, hasData }: any) {
    const [allCategories, setAllCategories] = useState<any[]>([]);
    const [allBrands, setAllBrands] = useState<any[]>([]);
    const [refreshKey, setRefreshKey] = useState(0);
    const [dateRange, setDateRange] = useState<DateRange>({
        from: startOfMonth(new Date()),
        to: endOfMonth(new Date()),
    });

    useEffect(() => {
        Promise.all([
            getAllCategories(),
            getAllBrands()
        ]).then(([{ data: categories }, { data: brands }]) => {
            setAllCategories(categories.allCategories);
            setAllBrands(brands.allBrands);
        }).catch(console.error);
    }, []);

    const handleDateChange = (newDateRange: DateRange | undefined) => {
        if (newDateRange?.from && newDateRange?.to) {
            setDateRange(newDateRange);
        }
    };

    const header = (
        <div className="flex items-center justify-between w-full">
            <h2>Dashboard</h2>
            <div className="flex items-center gap-2">
                <DatePickerWithRange
                    onDateChange={handleDateChange}
                    initialDate={dateRange}
                />
                <RecordTransactionButton
                    brands={allBrands}
                    onSuccess={() => setRefreshKey(prev => prev + 1)}
                />
            </div>
        </div>
    );

    const categoryRelation = useMemo(() => ({
        data: allCategories,
        display_using: 'name',
        foreign_key: 'id'
    }), [allCategories]);

    const categoryRelationForBrands = useMemo(() => ({
        data: allCategories,
        display_using: 'name',
        foreign_key: 'category_id'
    }), [allCategories]);

    const brandRelation = useMemo(() => ({
        data: allBrands,
        display_using: 'name',
        foreign_key: 'id'
    }), [allBrands]);

    return (
        <Authenticated auth={auth} header={header}>
            <Head title="Hisabi Dashboard" />

            <div className="py-4">
                <div className="max-w-7xl overflow-hidden mx-auto px-4 grid grid-cols-1 gap-4">

                    <Budgets key={`budgets-${refreshKey}`} />

                    {!hasData && <NoContent body="No enough data to show reports" />}

                    {hasData && (
                        <div className="grid grid-cols-1 gap-4">
                            {/* Net Worth - Full Width Trend */}
                            <div className="w-full">
                                <TrendMetric
                                    key={`netWorthTrend-${refreshKey}`}
                                    name="Net Worth Over Time"
                                    metric="netWorthTrend"
                                    dateRange={dateRange}
                                />
                            </div>

                            <div className="w-full grid grid-cols-1 md:grid-cols-3 gap-4"
                            >
                                <ValueMetric
                                    key={`totalCash-${refreshKey}`}
                                    name="Total Cash"
                                    metric="totalCash"
                                    helpText="The available cash = income - (expenses + savings + investments)"
                                    dateRange={dateRange}
                                />
                                <ValueMetric
                                    key={`totalSavings-${refreshKey}`}
                                    name="Total Savings"
                                    metric="totalSavings"
                                    dateRange={dateRange}
                                />
                                <ValueMetric
                                    key={`totalInvestment-${refreshKey}`}
                                    name="Total Investment"
                                    metric="totalInvestment"
                                    dateRange={dateRange}
                                />
                            </div>

                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <ValueMetric
                                    key={`totalIncome-${refreshKey}`}
                                    name="Total Income"
                                    metric="totalIncome"
                                    dateRange={dateRange}
                                />
                                <ValueMetric
                                    key={`totalExpenses-${refreshKey}`}
                                    name="Total Expenses"
                                    metric="totalExpenses"
                                    dateRange={dateRange}
                                />

                                <TrendMetric
                                    key={`totalIncomeTrend-${refreshKey}`}
                                    name="Income Over Time"
                                    metric="totalIncomeTrend"
                                    dateRange={dateRange}
                                />
                                <TrendMetric
                                    key={`totalExpensesTrend-${refreshKey}`}
                                    name="Spending Over Time"
                                    metric="totalExpensesTrend"
                                    dateRange={dateRange}
                                />
                            </div>


                            <SectionDivider title="Categories Analytics" />

                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <PartitionMetric
                                    key={`incomePerCategory-${refreshKey}`}
                                    name="Income Sources"
                                    metric="incomePerCategory"
                                    show_currency={true}
                                    dateRange={dateRange}
                                />
                                <PartitionMetric
                                    key={`expensesPerCategory-${refreshKey}`}
                                    name="Spending by Category"
                                    metric="expensesPerCategory"
                                    show_currency={true}
                                    dateRange={dateRange}
                                />

                                <TrendMetric
                                    key={`totalPerCategoryTrend-${refreshKey}`}
                                    name="Overall Trend by Category"
                                    metric="totalPerCategoryTrend"
                                    relation={categoryRelation}
                                    dateRange={dateRange}
                                />
                                <TrendMetric
                                    key={`totalPerCategoryDailyTrend-${refreshKey}`}
                                    name="Daily Trend by Category"
                                    metric="totalPerCategoryDailyTrend"
                                    relation={categoryRelation}
                                    dateRange={dateRange}
                                />
                            </div>

                            <SectionDivider title="Brands Analytics" />

                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <PartitionMetric
                                    key={`totalPerBrand-${refreshKey}`}
                                    name="Spending by Brand"
                                    metric="totalPerBrand"
                                    relation={categoryRelationForBrands}
                                    show_currency={true}
                                    dateRange={dateRange}
                                />
                                <TrendMetric
                                    key={`totalPerBrandTrend-${refreshKey}`}
                                    name="Overall Trend by Brand"
                                    metric="totalPerBrandTrend"
                                    relation={brandRelation}
                                    dateRange={dateRange}
                                />
                            </div>

                            <SectionDivider title="Finance Visualization" />

                            <div className="w-full">
                                <CirclePackMetric
                                    key={`financeVisualizationCirclePackMetric-${refreshKey}`}
                                    name="Finance Visualization"
                                    metric="financeVisualizationCirclePackMetric"
                                    dateRange={dateRange}
                                />
                            </div>

                        </div>
                    )}
                </div>
            </div>
        </Authenticated>
    );
}
