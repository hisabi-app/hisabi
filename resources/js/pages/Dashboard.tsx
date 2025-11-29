import { Head } from '@inertiajs/react';

import Authenticated from '@/Layouts/Authenticated';
import NoContent from '@/components/Global/NoContent';
import ValueMetric from '@/components/Domain/ValueMetric';
import TrendMetric from '@/components/Domain/TrendMetric';
import PartitionMetric from '@/components/Domain/PartitionMetric';
import CirclePackMetric from '@/components/Domain/CirclePackMetric';
import SectionDivider from '@/components/Global/SectionDivider';
import Budgets from '@/components/Domain/Budgets';
import { getAllCategories } from '@/Api/categories';
import { getAllBrands } from '@/Api/brands';

export default function Dashboard({ auth, hasData }: any) {
    const header = <h2>Dashboard</h2>

    const allRages = [
        { key: 'current-month', name: 'Current Month' },
        { key: 'last-month', name: 'Last Month' },
        { key: 'last-twelve-months', name: 'Last 12 Months' },
        { key: 'current-year', name: 'Current Year' },
        { key: 'last-year', name: 'Last Year' },
        { key: 'all-time', name: 'All Time' },
    ];

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
                                    graphql_query="netWorthTrend"
                                    ranges={allRages.slice().reverse()}
                                    relation={undefined}
                                    show_standard_deviation={undefined}
                                />
                            </div>

                            <div className="w-full grid grid-cols-1 md:grid-cols-3 gap-4"
                            >
                                <ValueMetric
                                    name="Total Cash"
                                    graphql_query="totalCash"
                                    ranges={null}
                                    helpText="The available cash = income - (expenses + savings + investments)"
                                />
                                <ValueMetric
                                    name="Total Savings"
                                    graphql_query="totalSavings"
                                    ranges={null}
                                    helpText={undefined}
                                />
                                <ValueMetric
                                    name="Total Investment"
                                    graphql_query="totalInvestment"
                                    ranges={null}
                                    helpText={undefined}
                                />
                            </div>

                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <ValueMetric
                                    name="Total Income"
                                    graphql_query="totalIncome"
                                    ranges={allRages}
                                    helpText={undefined}
                                />
                                <ValueMetric
                                    name="Total Expenses"
                                    graphql_query="totalExpenses"
                                    ranges={allRages}
                                    helpText={undefined}
                                />

                                <TrendMetric
                                    name="Income Over Time"
                                    graphql_query="totalIncomeTrend"
                                    ranges={allRages}
                                    relation={undefined}
                                    show_standard_deviation={undefined}
                                />
                                <TrendMetric
                                    name="Spending Over Time"
                                    graphql_query="totalExpensesTrend"
                                    ranges={allRages}
                                    relation={undefined}
                                    show_standard_deviation={undefined}
                                />
                            </div>


                            <SectionDivider title="Categories Analytics" />

                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <PartitionMetric
                                    name="Income Sources"
                                    graphql_query="incomePerCategory"
                                    ranges={allRages}
                                    relation={undefined}
                                    show_currency={true}
                                />
                                <PartitionMetric
                                    name="Spending by Category"
                                    graphql_query="expensesPerCategory"
                                    ranges={allRages}
                                    relation={undefined}
                                    show_currency={true}
                                />

                                <TrendMetric
                                    name="Overall Trend by Category"
                                    graphql_query="totalPerCategoryTrend"
                                    ranges={allRages}
                                    relation={categoryRelation}
                                    show_standard_deviation={undefined}
                                />
                                <TrendMetric
                                    name="Daily Trend by Category"
                                    graphql_query="totalPerCategoryDailyTrend"
                                    ranges={allRages}
                                    relation={categoryRelation}
                                    show_standard_deviation={undefined}
                                />
                            </div>

                            <SectionDivider title="Brands Analytics" />

                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <PartitionMetric
                                    name="Spending by Brand"
                                    graphql_query="totalPerBrand"
                                    ranges={allRages}
                                    relation={categoryRelationForBrands}
                                    show_currency={true}
                                />
                                <TrendMetric
                                    name="Overall Trend by Brand"
                                    graphql_query="totalPerBrandTrend"
                                    ranges={allRages}
                                    relation={brandRelation}
                                    show_standard_deviation={undefined}
                                />
                            </div>

                            <SectionDivider title="Finance Visualization" />

                            <div className="w-full">
                                <CirclePackMetric
                                    name="Finance Visualization"
                                    graphql_query="financeVisualizationCirclePackMetric"
                                    ranges={allRages}
                                />
                            </div>
                            
                        </div>
                    )}
                </div>
            </div>
        </Authenticated>
    );
}
