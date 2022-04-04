export default class Api {
    getTransactions(page) {
        return axios.post('/graphql', {query: `query {
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
         }`});
    }

    createTransaction({amount, brandId, createdAt, note}) {
        return axios.post('/graphql', {query: `mutation {
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
         }`});
    }
    
    updateTransaction({id, amount, brand, createdAt, note}) {
        return axios.post('/graphql', {query: `mutation {
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
         }`});
    }

    getAllBrands() {
        return axios.post('/graphql', {query: `query { 
            allBrands { 
                id 
                name
                category {
                    name
                }
            } 
        }`});
    }

    getBrands(page) {
        return axios.post('/graphql', {query: `query {
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
         }`});
    }

    createBrand({name, categoryId}) {
        return axios.post('/graphql', {query: `mutation {
            createBrand(name: """${name}""" category_id: ${categoryId}) {
                id
                name
                category {
                    id
                    name
                }
            }
         }`});
    }

    updateBrand({id, name, category}) {
        return axios.post('/graphql', {query: `mutation {
            updateBrand(id: ${id} name: "${name}" category_id: ${category}) {
                id
                name
                category {
                    id
                    name
                }
            }
         }`});
    }

    getAllCategories() {
        return axios.post('/graphql', {query: `query { 
            allCategories { 
                id 
                name
            } 
        }`});
    }

    getCategories(page) {
        return axios.post('/graphql', {query: `query {
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
         }`});
    }

    createCategory({name, type}) {
        return axios.post('/graphql', {query: `mutation {
            createCategory(name: """${name}""" type: """${type}""") {
                id
                name
                type
            }
         }`});
    }

    updateCategory({id, name, type}) {
        return axios.post('/graphql', {query: `mutation {
            updateCategory(id: ${id} name: """${name}""" type: """${type}""") {
                id
                name
                type
            }
         }`});
    }

    getSms(page) {
        return axios.post('/graphql', {query: `query {
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
         }`});
    }

    createSms({sms}) {
        return axios.post('/graphql', {query: `mutation {
            createSms(body: """${sms}""") {
                id
                body
                transaction_id
            }
         }`});
    }

    updateSms({id, body}) {
        return axios.post('/graphql', {query: `mutation { 
            updateSms(id: ${id} body: """${body}""") { 
                id
                body
                transaction_id
            } 
        }`});
    }

    delete({id, resource}) {
        return axios.post('/graphql', {query: `mutation { 
            delete${resource}(id: ${id}) { 
                id
            } 
        }`});
    }

    query(query, range = null) {
        if(range == null) {
            return axios.post('/graphql', {query: `query { ${query} }`});    
        }

        return axios.post('/graphql', {query: `query { 
            ${query}(range: """${range}""") 
        }`});
    }
}