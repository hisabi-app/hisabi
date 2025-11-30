import { Head } from '@inertiajs/react';

import Authenticated from '@/Layouts/Authenticated';
import NoContent from '@/components/Global/NoContent';
import ValueMetric from '@/components/Domain/ValueMetric';
import TrendMetric from '@/components/Domain/TrendMetric';
import PartitionMetric from '@/components/Domain/PartitionMetric';
import CirclePackMetric from '@/components/Domain/CirclePackMetric';
import SectionDivider from '@/components/Global/SectionDivider';
import Budgets from '@/components/Domain/Budgets';
import RangeSelector from '@/components/Global/RangeSelector';
import { RangeProvider } from '@/contexts/RangeContext';
import { getAllCategories } from '@/Api/categories';
import { getAllBrands } from '@/Api/brands';

export default function Dashboard({ auth, hasData }: any) {
    const header = (
        <div className="flex items-center justify-between w-full">
            <h2>Dashboard</h2>
            <RangeSelector />
        </div>
    );

    const categoryRelation = {
        fetcher: getAllCategories,
        data_key: 'allCategories',
        display_using: 'name',
        foreign_key: 'id'
    };

    const categoryRelationForBrands = {
        fetcher: getAllCategories,
        data_key: 'allCategories',
        display_using: 'name',
        foreign_key: 'category_id'
    };

    const brandRelation = {
        fetcher: getAllBrands,
        data_key: 'allBrands',
        display_using: 'name',
        foreign_key: 'id'
    };

    return (
        <RangeProvider>
            <Authenticated auth={auth} header={header}>
                <Head title="Hisabi Dashboard" />

                <div className="py-4">
                    <div className="max-w-7xl overflow-hidden mx-auto px-4 grid grid-cols-1 gap-4">

                        <Budgets />

                        {!hasData && <NoContent body="No enough data to show reports" />}

                        {hasData && (
                            <div className="grid grid-cols-1 gap-4">
                                {/* Net Worth - Full Width Trend */}
                                <div className="w-full">
                                    <TrendMetric
                                        name="Net Worth Over Time"
                                        metric="netWorthTrend"
                                        relation={undefined}
                                        show_standard_deviation={undefined}
                                    />
                                </div>

                                <div className="w-full grid grid-cols-1 md:grid-cols-3 gap-4"
                                >
                                    <ValueMetric
                                        name="Total Cash"
                                        metric="totalCash"
                                        helpText="The available cash = income - (expenses + savings + investments)"
                                    />
                                    <ValueMetric
                                        name="Total Savings"
                                        metric="totalSavings"
                                        helpText={undefined}
                                    />
                                    <ValueMetric
                                        name="Total Investment"
                                        metric="totalInvestment"
                                        helpText={undefined}
                                    />
                                </div>

                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <ValueMetric
                                        name="Total Income"
                                        metric="totalIncome"
                                        helpText={undefined}
                                    />
                                    <ValueMetric
                                        name="Total Expenses"
                                        metric="totalExpenses"
                                        helpText={undefined}
                                    />

                                    <TrendMetric
                                        name="Income Over Time"
                                        metric="totalIncomeTrend"
                                        relation={undefined}
                                        show_standard_deviation={undefined}
                                    />
                                    <TrendMetric
                                        name="Spending Over Time"
                                        metric="totalExpensesTrend"
                                        relation={undefined}
                                        show_standard_deviation={undefined}
                                    />
                                </div>


                                <SectionDivider title="Categories Analytics" />

                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <PartitionMetric
                                        name="Income Sources"
                                        metric="incomePerCategory"
                                        relation={undefined}
                                        show_currency={true}
                                    />
                                    <PartitionMetric
                                        name="Spending by Category"
                                        metric="expensesPerCategory"
                                        relation={undefined}
                                        show_currency={true}
                                    />

                                    <TrendMetric
                                        name="Overall Trend by Category"
                                        metric="totalPerCategoryTrend"
                                        relation={categoryRelation}
                                        show_standard_deviation={undefined}
                                    />
                                    <TrendMetric
                                        name="Daily Trend by Category"
                                        metric="totalPerCategoryDailyTrend"
                                        relation={categoryRelation}
                                        show_standard_deviation={undefined}
                                    />
                                </div>

                                <SectionDivider title="Brands Analytics" />

                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <PartitionMetric
                                        name="Spending by Brand"
                                        metric="totalPerBrand"
                                        relation={categoryRelationForBrands}
                                        show_currency={true}
                                    />
                                    <TrendMetric
                                        name="Overall Trend by Brand"
                                        metric="totalPerBrandTrend"
                                        relation={brandRelation}
                                        show_standard_deviation={undefined}
                                    />
                                </div>

                                <SectionDivider title="Finance Visualization" />

                                <div className="w-full">
                                    <CirclePackMetric
                                        name="Finance Visualization"
                                        metric="financeVisualizationCirclePackMetric"
                                    />
                                </div>

                            </div>
                        )}
                    </div>
                </div>
            </Authenticated>
        </RangeProvider>
    );
}
