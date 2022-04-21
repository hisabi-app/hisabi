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

export const getBrands = (page) => {
    return client
            .query(gql`
                query {
                    brands(page: ${page}) {
                        data {
                            id
                            name
                            category {
                                id
                                name
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
                }
            }
        `)
        .toPromise();
}

export const updateBrand = ({id, name, category}) => {
    return client
        .mutation(gql`
            mutation {
                updateBrand(id: ${id} name: "${name}" category_id: ${category}) {
                    id
                    name
                    category {
                        id
                        name
                    }
                }
            }
        `)
        .toPromise();
}