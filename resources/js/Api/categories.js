import { gql } from '@urql/core';
import client from './client.js';
import { customQuery } from './common.js';

export const getAllCategories = async () => {
    const response = await fetch('/api/v1/categories/all', {
        method: 'GET',
    });

    if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
    }

    const result = await response.json();

    return {
        data: {
            allCategories: result.data
        }
    };
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
