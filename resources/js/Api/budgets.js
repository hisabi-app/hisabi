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
