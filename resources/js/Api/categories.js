import { getCsrfToken } from './common.js';
import { getCategoryStats as getCategoryStatsMetric } from './metrics.js';

export const getAllCategories = async () => {
    const response = await fetch('/api/v1/categories/all', {
        method: 'GET',
    });

    if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
    }

    const result = await response.json();

    return {
        data: {
            allCategories: result.data
        }
    };
}

export const createCategory = async ({name, type, color, icon}) => {
    const response = await fetch('/api/v1/categories', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': getCsrfToken(),
            'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin',
        body: JSON.stringify({ name, type, color, icon })
    });

    if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
    }

    const result = await response.json();

    return {
        data: {
            createCategory: {
                ...result.category,
                transactionsCount: result.category.transactions_count
            }
        }
    };
}

export const updateCategory = async ({id, name, type, color, icon}) => {
    const response = await fetch(`/api/v1/categories/${id}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': getCsrfToken(),
            'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin',
        body: JSON.stringify({ name, type, color, icon })
    });

    if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
    }

    const result = await response.json();

    return {
        data: {
            updateCategory: {
                ...result.category,
                transactionsCount: result.category.transactions_count
            }
        }
    };
}

export const deleteCategory = async (id) => {
    const response = await fetch(`/api/v1/categories/${id}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': getCsrfToken(),
            'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin',
    });

    if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
    }

    const result = await response.json();

    return {
        data: {
            deleteCategory: result.category
        }
    };
}

export const getCategoryStats = async (range = 'current-month') => {
    const response = await getCategoryStatsMetric(range);
    return {
        data: response.data
    };
}
