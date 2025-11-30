import { getCsrfToken } from './common.js';
import { getTotalIncome, getTotalExpenses, getTransactionsCount } from './metrics.js';

export const getTransactions = async (page, searchQuery, filters = {}) => {
    const params = new URLSearchParams({
        page: page.toString(),
        perPage: '100'
    });
    
    if (searchQuery) {
        params.append('filter[search]', searchQuery);
    }

    if (filters.brandId) {
        params.append('filter[brand_id]', filters.brandId);
    }
    if (filters.categoryId) {
        params.append('filter[category_id]', filters.categoryId);
    }
    if (filters.dateFrom) {
        params.append('filter[date_from]', filters.dateFrom);
    }
    if (filters.dateTo) {
        params.append('filter[date_to]', filters.dateTo);
    }

    const response = await fetch(`/api/v1/transactions?${params.toString()}`, {
        method: 'GET',
    });

    if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
    }

    const data = await response.json();
    
    return {
        data: {
            transactions: data
        }
    };
}

export const createTransaction = async ({amount, brandId, createdAt, note}) => {
    const response = await fetch('/api/v1/transactions', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': getCsrfToken(),
            'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin',
        body: JSON.stringify({
            amount,
            brand_id: brandId,
            created_at: createdAt,
            note
        })
    });

    if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
    }

    const data = await response.json();

    return {
        data: data
    };
}

export const updateTransaction = async ({id, amount, brandId, createdAt, note}) => {
    const response = await fetch(`/api/v1/transactions/${id}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': getCsrfToken(),
            'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin',
        body: JSON.stringify({
            amount,
            brand_id: brandId,
            created_at: createdAt,
            note
        })
    });

    if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
    }

    const data = await response.json();

    return {
        data: data
    };
}

export const deleteTransaction = async (id) => {
    const response = await fetch(`/api/v1/transactions/${id}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': getCsrfToken(),
            'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin'
    });

    if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
    }

    const data = await response.json();

    return {
        data: data
    };
}

export const getTransactionStats = async (dateRange) => {
    const [incomeRes, expensesRes, countRes] = await Promise.all([
        getTotalIncome(dateRange),
        getTotalExpenses(dateRange),
        getTransactionsCount(dateRange)
    ]);

    return {
        data: {
            totalIncome: incomeRes.data,
            totalExpenses: expensesRes.data,
            numberOfTransactions: countRes.data
        }
    };
}
