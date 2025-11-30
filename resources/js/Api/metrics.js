const getMetric = async (endpoint, params = {}) => {
    const filteredParams = Object.fromEntries(
        Object.entries(params).filter(([_, v]) => v != null)
    );
    const searchParams = new URLSearchParams(filteredParams);
    const url = searchParams.toString()
        ? `/api/v1/metrics/${endpoint}?${searchParams}`
        : `/api/v1/metrics/${endpoint}`;

    const response = await fetch(url, {
        method: 'GET',
        credentials: 'same-origin',
    });

    if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
    }

    return await response.json();
};

export const getTotalIncome = (range) => getMetric('total-income', { range });
export const getTotalExpenses = (range) => getMetric('total-expenses', { range });
export const getTotalSavings = () => getMetric('total-savings');
export const getTotalInvestment = () => getMetric('total-investment');
export const getTotalCash = () => getMetric('total-cash');
export const getNetWorth = () => getMetric('net-worth');

export const getNetWorthTrend = (range) => getMetric('net-worth-trend', { range });
export const getTotalIncomeTrend = (range) => getMetric('total-income-trend', { range });
export const getTotalExpensesTrend = (range) => getMetric('total-expenses-trend', { range });
export const getCategoryTrend = (range, id) => getMetric('category-trend', { range, id });
export const getCategoryDailyTrend = (range, id) => getMetric('category-daily-trend', { range, id });
export const getBrandTrend = (range, id) => getMetric('brand-trend', { range, id });
export const getBrandChangeRate = (range, id) => getMetric('brand-change-rate', { range, id });

export const getExpensesByCategory = (range) => getMetric('expenses-by-category', { range });
export const getIncomeByCategory = (range) => getMetric('income-by-category', { range });
export const getSpendingByBrand = (range, category_id) => getMetric('spending-by-brand', { range, category_id });

export const getTransactionsCount = (range) => getMetric('transactions-count', { range });
export const getTransactionsByCategory = (range) => getMetric('transactions-by-category', { range });
export const getTransactionsByBrand = (range, id) => getMetric('transactions-by-brand', { range, id });
export const getHighestTransaction = (range) => getMetric('highest-transaction', { range });
export const getLowestTransaction = (range) => getMetric('lowest-transaction', { range });
export const getAverageTransaction = (range) => getMetric('average-transaction', { range });
export const getTransactionsStdDev = (range, id) => getMetric('transactions-std-dev', { range, id });
export const getBrandStats = (range) => getMetric('brand-stats', { range });
export const getCategoryStats = (range) => getMetric('category-stats', { range });

export const getCirclePack = (range) => getMetric('circle-pack', { range });

export const metricEndpoints = {
    totalIncome: getTotalIncome,
    totalExpenses: getTotalExpenses,
    totalSavings: getTotalSavings,
    totalInvestment: getTotalInvestment,
    totalCash: getTotalCash,
    netWorth: getNetWorth,
    netWorthTrend: getNetWorthTrend,
    totalIncomeTrend: getTotalIncomeTrend,
    totalExpensesTrend: getTotalExpensesTrend,
    totalPerCategoryTrend: getCategoryTrend,
    totalPerCategoryDailyTrend: getCategoryDailyTrend,
    totalPerBrandTrend: getBrandTrend,
    changeRatePerBrandTrend: getBrandChangeRate,
    expensesPerCategory: getExpensesByCategory,
    incomePerCategory: getIncomeByCategory,
    totalPerBrand: getSpendingByBrand,
    numberOfTransactions: getTransactionsCount,
    numberOfTransactionsPerCategory: getTransactionsByCategory,
    numberOfTransactionsPerBrand: getTransactionsByBrand,
    highestValueTransaction: getHighestTransaction,
    lowestValueTransaction: getLowestTransaction,
    averageValueTransaction: getAverageTransaction,
    transactionsStandardDeviation: getTransactionsStdDev,
    brandStats: getBrandStats,
    categoryStats: getCategoryStats,
    financeVisualizationCirclePackMetric: getCirclePack,
};
