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

// The kiosk stays open for days, so the token in the page can go stale (a new login replaces it).
// The XSRF-TOKEN cookie comes back fresh with every response, the hourly summary included.
const xsrfToken = () => {
    const cookie = document.cookie.split('; ').find((part) => part.startsWith('XSRF-TOKEN='));

    return cookie ? decodeURIComponent(cookie.slice('XSRF-TOKEN='.length)) : '';
};

const failure = async (response) => {
    const error = new Error((await response.json().catch(() => null))?.message ?? `HTTP error! status: ${response.status}`);
    error.status = response.status;

    return error;
};

const post = async (url, body, retried = false) => {
    const response = await fetch(url, {
        method: 'POST',
        body,
        headers: { Accept: 'application/json', 'X-XSRF-TOKEN': xsrfToken(), 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin',
    });

    // A stale token: any request hands out a fresh cookie, then try once more
    if (response.status === 419 && !retried) {
        await fetch('/api/v1/kiosk', { headers: { Accept: 'application/json' }, credentials: 'same-origin' });

        return post(url, body, true);
    }

    if (!response.ok) throw await failure(response);

    return (await response.json()).data;
};

export const transcribeQuestion = async (recording) => {
    const body = new FormData();
    body.append('audio', recording, 'question');

    return (await post('/api/v1/kiosk/transcribe', body)).text;
};

export const askQuestion = async ({ question, conversationId }) => {
    const body = new FormData();
    body.append('question', question);
    if (conversationId) body.append('conversation_id', conversationId);

    return post('/api/v1/kiosk/ask', body);
};

// The answer read out, as mp3 bytes for the page's AudioContext
export const getSpeech = async (speechId) => {
    const response = await fetch(`/api/v1/kiosk/speech/${speechId}`, { credentials: 'same-origin' });

    if (!response.ok) throw await failure(response);

    return response.arrayBuffer();
};
