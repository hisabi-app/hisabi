import { gql } from '@urql/core';
import client from './client.js';

export const getSms = (page) => {
    return client
            .query(gql`
                query {
                    sms(page: ${page}) {
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

export const createSms = ({sms}) => {
    return client
        .mutation(gql`
            mutation {
                createSms(body: """${sms}""") {
                    id
                    body
                    transaction_id
                }
            }
        `)
        .toPromise();
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