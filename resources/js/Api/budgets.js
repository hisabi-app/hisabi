export const getBudgets = async () => {
    const response = await fetch('/api/v1/budgets', {
        method: 'GET',
    });

    if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
    }

    const result = await response.json();

    return {
        data: {
            budgets: result.data
        }
    };
}

export const getBudgetDailySpending = async (budgetId) => {
    const response = await fetch(`/api/v1/budgets/${budgetId}/daily-spending`, {
        method: 'GET',
    });

    if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
    }

    const result = await response.json();

    return result.data;
}
