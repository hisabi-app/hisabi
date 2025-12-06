import { getCsrfToken } from './common.js';

export const updateUserProfile = async ({ name, password }) => {
    const body = { name };

    // Only include password if it's provided
    if (password) {
        body.password = password;
    }

    const response = await fetch('/api/v1/user/profile', {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': getCsrfToken(),
            'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin',
        body: JSON.stringify(body)
    });

    if (!response.ok) {
        const errorData = await response.json().catch(() => ({}));
        throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
    }

    const result = await response.json();

    return {
        data: {
            user: result.user
        }
    };
}
