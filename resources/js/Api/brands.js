import { gql } from '@urql/core';
import client from './client.js';
import { customQuery, getCsrfToken } from './common.js';

export const getAllBrands = async () => {
    const response = await fetch('/api/v1/brands/all', {
        method: 'GET',
    });

    if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
    }

    const result = await response.json();

    return {
        data: {
            allBrands: result.data
        }
    };
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

export const createBrand = async ({name, categoryId}) => {
    const response = await fetch('/api/v1/brands', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': getCsrfToken(),
            'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin',
        body: JSON.stringify({
            name: name,
            category_id: categoryId
        })
    });

    if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
    }

    const result = await response.json();

    return {
        data: {
            createBrand: result.brand
        }
    };
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
