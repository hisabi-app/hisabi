import { gql } from '@urql/core';
import client from './client.js';
import { customQuery } from './common.js';

export const getAllCategories = () => {
    return client
            .query(gql`
                query {
                    allCategories {
                        id
                        name
                        type
                        color
                        icon
                        transactionsCount
                    }
                }
            `)
            .toPromise();
}

export const getCategories = (page, searchQuery) => {
    return client
        .query(gql`
            query {
                categories(search: """${searchQuery}""" page: ${page}) {
                    data {
                        id
                        name
                        type
                        color
                        icon
                        transactionsCount
                    }
                    paginatorInfo {
                        hasMorePages
                    }
                }
            }
        `)
        .toPromise();
}

export const createCategory = ({name, type, color, icon}) => {
    return client
        .mutation(gql`
            mutation {
                createCategory(name: """${name}""" type: """${type}""" color: """${color}""" icon: """${icon}""") {
                    id
                    name
                    type
                    color
                    icon
                    transactionsCount
                }
            }
        `)
        .toPromise();
}

export const updateCategory = ({id, name, type, color, icon}) => {
    return client
        .mutation(gql`
            mutation {
                updateCategory(id: ${id} name: """${name}""" type: """${type}""" color: """${color}""" icon: """${icon}""") {
                    id
                    name
                    type
                    color
                    icon
                    transactionsCount
                }
            }
        `)
        .toPromise();
}

export const getCategoryStats = (range = 'current-month') => {
    return customQuery(`categoryStats(range: "${range}")`);
}
