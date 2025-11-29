import { gql } from '@urql/core';
import client from './client.js';
import { getCsrfToken } from './common.js';

export const getSms = (page, searchQuery) => {
    return client
            .query(gql`
                query {
                    sms(search: """${searchQuery}""" page: ${page}) {
                        data {
                            id
                            body
                            transaction_id
                        }
                        paginatorInfo {
                            hasMorePages
                        }
                    }
                }
            `)
            .toPromise();
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
