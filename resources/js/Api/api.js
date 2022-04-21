import { createClient, gql } from '@urql/core';
import 'isomorphic-unfetch';

// move client to file
// split models to files
// fix cache issue
// find alternative to toPromise?

const client = createClient({
  url: '/graphql',
  maskTypename: true,
  fetchOptions: () => {
    const token = document.head.querySelector('meta[name="csrf-token"]');

    return {
        credentials: 'include',
        headers: { 
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN' : token ? token.content : '',
        },
    };
  },
});

export const deleteResource = ({id, resource}) => {
    return client
        .mutation(gql`
            mutation {
                delete${resource}(id: ${id}) {
                    id
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

export const query = (query, range = null) => {
    if(range == null) {
        return client
            .query(gql`
                query {
                    ${query}
                }
            `)
            .toPromise();
    }
    
    return client
            .query(gql`
                query {
                    ${query}(range: """${range}""") 
                }
            `)
            .toPromise();
}


export const getTransactions = (page) => {
    return client
            .query(gql`
                query {
                    transactions(page: ${page}) {
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
    
export const updateTransaction = ({id, amount, brand, createdAt, note}) => {
    return client
        .mutation(gql`
            mutation {
                updateTransaction(id: ${id} amount: ${amount} brand_id: ${brand} created_at: """${createdAt}""" note: """${note}""") {
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