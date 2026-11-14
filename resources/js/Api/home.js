import { getCsrfToken } from './common.js';

const json = async (response) => {
    if (!response.ok) {
        const error = new Error(`HTTP error! status: ${response.status}`);
        error.status = response.status;
        throw error;
    }

    return (await response.json()).data;
};

export const getHome = async () => json(await fetch('/api/v1/home', { headers: { Accept: 'application/json' } }));

export const getBudgetDetail = async (id) => json(await fetch(`/api/v1/budgets/${id}/detail`, { headers: { Accept: 'application/json' } }));

// Hides a suggestion until next month
export const dismissSuggestion = async (key) =>
    json(
        await fetch('/api/v1/home/dismissals', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
            body: JSON.stringify({ key }),
        }),
    );
