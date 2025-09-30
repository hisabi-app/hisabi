import { gql } from '@urql/core';
import client from './client.js';
import { customQuery } from './common.js';

export const getTransactions = (page, searchQuery) => {
    return client
            .query(gql`
                query {
                    transactions(search: """${searchQuery}""" page: ${page}) {
                        data {
                            id
                            amount
                            created_at
                            note
                            brand {
                                id
                                name
                                category {
                                    name
                                    type
                                    color
                                }
                            }
                        }
                        paginatorInfo {
                            hasMorePages
                        }
                    }
                }
            `)
            .toPromise();
}

export const createTransaction = ({amount, brandId, createdAt, note}) => {
    return client
        .mutation(gql`
            mutation {
                createTransaction(amount: ${amount} brand_id: ${brandId} created_at: """${createdAt}""" note: """${note}""") {
                    id
                    amount
                    created_at
                    note
                    brand {
                        id
                        name
                        category {
                            name
                            type
                        }
                    }
                }
            }
        `)
        .toPromise();
}

export const updateTransaction = ({id, amount, brandId, createdAt, note}) => {
    return client
        .mutation(gql`
            mutation {
                updateTransaction(id: ${id} amount: ${amount} brand_id: ${brandId} created_at: """${createdAt}""" note: """${note}""") {
                    id
                    amount
                    created_at
                    note
                    brand {
                        id
                        name
                        category {
                            name
                            type
                        }
                    }
                }
            }
        `)
        .toPromise();
}

export const getTransactionStats = () => {
    return customQuery(`
        totalIncome(range: "all-time")
        totalExpenses(range: "all-time")
        numberOfTransactions(range: "all-time")
    `);
}
