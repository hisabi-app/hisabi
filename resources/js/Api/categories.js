import { gql } from '@urql/core';
import client from './client.js';

export const getAllCategories = () => {
    return client
            .query(gql`
                query {
                    allCategories { 
                        id 
                        name
                        color
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
                        color
                    }
                    paginatorInfo {
                        hasMorePages
                    }
                }
            }
        `)
        .toPromise();
}

export const createCategory = ({name, type, color}) => {
    return client
        .mutation(gql`
            mutation {
                createCategory(name: """${name}""" type: """${type}""" color: """${color}""") {
                    id
                    name
                    type
                    color
                }
            }
        `)
        .toPromise();
}

export const updateCategory = ({id, name, type, color}) => {
    return client
        .mutation(gql`
            mutation {
                updateCategory(id: ${id} name: """${name}""" type: """${type}""" color: """${color}""") {
                    id
                    name
                    type
                    color
                }
            }
        `)
        .toPromise();
}