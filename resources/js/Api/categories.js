import { gql } from '@urql/core';
import client from './client.js';

export const getAllCategories = () => {
    return client
            .query(gql`
                query {
                    allCategories { 
                        id 
                        name
                    } 
                }
            `)
            .toPromise();
}

export const getCategories = (page) => {
    return client
        .query(gql`
            query {
                categories(page: ${page}) {
                    data {
                        id
                        name
                        type
                    }
                    paginatorInfo {
                        hasMorePages
                    }
                }
            }
        `)
        .toPromise();
}

export const createCategory = ({name, type}) => {
    return client
        .mutation(gql`
            mutation {
                createCategory(name: """${name}""" type: """${type}""") {
                    id
                    name
                    type
                }
            }
        `)
        .toPromise();
}

export const updateCategory = ({id, name, type}) => {
    return client
        .mutation(gql`
            mutation {
                updateCategory(id: ${id} name: """${name}""" type: """${type}""") {
                    id
                    name
                    type
                }
            }
        `)
        .toPromise();
}