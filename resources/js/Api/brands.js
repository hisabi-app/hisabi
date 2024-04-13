import { gql } from '@urql/core';
import client from './client.js';

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

export const getBrands = (page, searchQuery) => {
    return client
            .query(gql`
                query {
                    brands(search: """${searchQuery}""" page: ${page}) {
                        data {
                            id
                            name
                            category {
                                id
                                name
                                color
                            }
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
