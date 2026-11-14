import { getCsrfToken } from './common.js';

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

export const getBudgetsHistory = async ({ from, to } = {}) => {
    const params = new URLSearchParams();
    if (from) params.set('from', from);
    if (to) params.set('to', to);

    const response = await fetch(`/api/v1/budgets/history?${params}`, {
        method: 'GET',
    });

    if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
    }

    const result = await response.json();

    return {
        budgets: result.data,
        meta: result.meta,
    };
}

export const updateBudget = async ({ id, amount }) => {
    const response = await fetch(`/api/v1/budgets/${id}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': getCsrfToken(),
            'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin',
        body: JSON.stringify({ amount }),
    });

    if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
    }

    const result = await response.json();

    return result.budget;
}

export const createBudget = async ({ name, amount, reoccurrence, startAt, endAt, categoryIds }) => {
    const response = await fetch('/api/v1/budgets', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': getCsrfToken(),
            'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin',
        body: JSON.stringify({ name, amount, reoccurrence, start_at: startAt, end_at: endAt, category_ids: categoryIds }),
    });

    if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
    }

    const result = await response.json();

    return result.budget;
}

export const deleteBudget = async (id) => {
    const response = await fetch(`/api/v1/budgets/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': getCsrfToken(),
            'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin',
    });

    if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
    }

    const result = await response.json();

    return result.budget;
}
