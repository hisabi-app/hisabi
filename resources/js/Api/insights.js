const metric = async (endpoint, params = {}) => {
    const query = new URLSearchParams(Object.entries(params).filter(([, value]) => value != null));
    const response = await fetch(`/api/v1/metrics/${endpoint}${query.toString() ? `?${query}` : ''}`, {
        headers: { Accept: 'application/json' },
        credentials: 'same-origin',
    });

    if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
    }

    return (await response.json()).data;
};

// `range` is { from: 'yyyy-MM-dd', to: 'yyyy-MM-dd' } or null for all time
export const getNetWorth = () => metric('net-worth');
export const getNetWorthTrend = (range) => metric('net-worth-trend', range ?? {});
export const getIncomeAllocation = (range) => metric('income-allocation', range ?? {});
export const getCategoryBreakdown = (range) => metric('category-breakdown', range ?? {});
export const getTopBrands = (range) => metric('top-brands', range ?? {});
export const getSpendingOverTime = (range, by = 'month') => metric('spending-over-time', { ...(range ?? {}), by });
