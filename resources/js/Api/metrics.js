const formatDate = (date) => {
    if (!date) return null;
    const d = new Date(date);
    return d.toISOString().split('T')[0];
};

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

export const getTotalIncome = (dateRange) => getMetric('total-income', { from: formatDate(dateRange?.from), to: formatDate(dateRange?.to) });
export const getTotalExpenses = (dateRange) => getMetric('total-expenses', { from: formatDate(dateRange?.from), to: formatDate(dateRange?.to) });
export const getTotalSavings = () => getMetric('total-savings');
export const getTotalInvestment = () => getMetric('total-investment');
export const getTotalCash = () => getMetric('total-cash');
export const getNetWorth = () => getMetric('net-worth');

export const getNetWorthTrend = (dateRange) => getMetric('net-worth-trend', { from: formatDate(dateRange?.from), to: formatDate(dateRange?.to) });
export const getTotalIncomeTrend = (dateRange) => getMetric('total-income-trend', { from: formatDate(dateRange?.from), to: formatDate(dateRange?.to) });
export const getTotalExpensesTrend = (dateRange) => getMetric('total-expenses-trend', { from: formatDate(dateRange?.from), to: formatDate(dateRange?.to) });
export const getCategoryTrend = (dateRange, id) => getMetric('category-trend', { from: formatDate(dateRange?.from), to: formatDate(dateRange?.to), id });
export const getCategoryDailyTrend = (dateRange, id) => getMetric('category-daily-trend', { from: formatDate(dateRange?.from), to: formatDate(dateRange?.to), id });
export const getBrandTrend = (dateRange, id) => getMetric('brand-trend', { from: formatDate(dateRange?.from), to: formatDate(dateRange?.to), id });
export const getBrandChangeRate = (dateRange, id) => getMetric('brand-change-rate', { from: formatDate(dateRange?.from), to: formatDate(dateRange?.to), id });

export const getExpensesByCategory = (dateRange) => getMetric('expenses-by-category', { from: formatDate(dateRange?.from), to: formatDate(dateRange?.to) });
export const getIncomeByCategory = (dateRange) => getMetric('income-by-category', { from: formatDate(dateRange?.from), to: formatDate(dateRange?.to) });
export const getSpendingByBrand = (dateRange, category_id) => getMetric('spending-by-brand', { from: formatDate(dateRange?.from), to: formatDate(dateRange?.to), category_id });

export const getTransactionsCount = (dateRange) => getMetric('transactions-count', { from: formatDate(dateRange?.from), to: formatDate(dateRange?.to) });
export const getTransactionsByCategory = (dateRange) => getMetric('transactions-by-category', { from: formatDate(dateRange?.from), to: formatDate(dateRange?.to) });
export const getTransactionsByBrand = (dateRange, id) => getMetric('transactions-by-brand', { from: formatDate(dateRange?.from), to: formatDate(dateRange?.to), id });
export const getHighestTransaction = (dateRange) => getMetric('highest-transaction', { from: formatDate(dateRange?.from), to: formatDate(dateRange?.to) });
export const getLowestTransaction = (dateRange) => getMetric('lowest-transaction', { from: formatDate(dateRange?.from), to: formatDate(dateRange?.to) });
export const getAverageTransaction = (dateRange) => getMetric('average-transaction', { from: formatDate(dateRange?.from), to: formatDate(dateRange?.to) });
export const getTransactionsStdDev = (dateRange, id) => getMetric('transactions-std-dev', { from: formatDate(dateRange?.from), to: formatDate(dateRange?.to), id });
export const getBrandStats = (dateRange) => getMetric('brand-stats', { from: formatDate(dateRange?.from), to: formatDate(dateRange?.to) });
export const getCategoryStats = (dateRange) => getMetric('category-stats', { from: formatDate(dateRange?.from), to: formatDate(dateRange?.to) });

export const getCirclePack = (dateRange) => getMetric('circle-pack', { from: formatDate(dateRange?.from), to: formatDate(dateRange?.to) });

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
