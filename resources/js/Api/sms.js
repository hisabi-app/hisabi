import { getCsrfToken } from './common.js';

export const getSms = async (page) => {
    const response = await fetch(`/api/v1/sms?page=${page}`, {
        method: 'GET',
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
    return { data: { sms: result } };
}

export const createSms = async ({sms, createdAt}) => {
    const response = await fetch(`/api/v1/sms`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': getCsrfToken(),
            'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin',
        body: JSON.stringify({
            body: sms,
            created_at: createdAt || null
        })
    });

    if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
    }

    const result = await response.json();
    return { data: { createSms: result.data } };
}

export const updateSms = ({id, body}) => {
    return client
        .mutation(gql`
            mutation {
                updateSms(id: ${id} body: """${body}""") {
                    id
                    body
                    transaction_id
                }
            }
        `)
        .toPromise();
}
