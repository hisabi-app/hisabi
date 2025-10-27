import { gql } from '@urql/core';
import client from './client.js';
import { customQuery } from './common.js';

export const getAllBrands = () => {
    return client
            .query(gql`
                query {
                    allBrands {
                        id
                        name
                        category {
                            name
                        }
                    }
                }
            `)
            .toPromise();
}

export const getBrands = async (page, searchQuery) => {
    const params = new URLSearchParams({
        page: page.toString(),
        perPage: '50'
    });

    if (searchQuery) {
        params.append('filter[search]', searchQuery);
    }

    const response = await fetch(`/api/v1/brands?${params.toString()}`, {
        method: 'GET',
    });

    if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
    }

    const data = await response.json();

    return {
        data: {
            brands: data
        }
    };
}

export const createBrand = ({name, categoryId}) => {
    return client
        .mutation(gql`
            mutation {
                createBrand(name: """${name}""" category_id: ${categoryId}) {
                    id
                    name
                    category {
                        id
                        name
                    }
                    transactionsCount
                }
            }
        `)
        .toPromise();
}

export const updateBrand = ({id, name, categoryId}) => {
    return client
        .mutation(gql`
            mutation {
                updateBrand(id: ${id} name: "${name}" category_id: ${categoryId}) {
                    id
                    name
                    category {
                        id
                        name
                    }
                    transactionsCount
                }
            }
        `)
        .toPromise();
}

export const getBrandStats = (range = 'current-month') => {
    return customQuery(`brandStats(range: "${range}")`);
}
