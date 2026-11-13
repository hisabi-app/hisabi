export const getKioskSummary = async () => {
    const response = await fetch('/api/v1/kiosk', {
        method: 'GET',
        // Without it an expired session redirects to the login page instead of returning 401
        headers: { Accept: 'application/json' },
    });

    if (!response.ok) {
        const error = new Error(`HTTP error! status: ${response.status}`);
        error.status = response.status;
        throw error;
    }

    const result = await response.json();

    return result.data;
};
