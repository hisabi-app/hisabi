export default class Api {
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

    getSms(page) {
        return axios.post('/graphql', {query: `query {
            sms(page: ${page}) {
                data {
                    id
                    body
                    transaction_id
                    meta
                }
                paginatorInfo {
                    hasMorePages
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

    getTransactions(page) {
        return axios.post('/graphql', {query: `query {
            transactions(page: ${page}) {
                data {
                    id
                    amount
                    category {
                        name
                        type
                    }
                    brand {
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

    updateTransaction({id, amount, brand}) {
        return axios.post('/graphql', {query: `mutation {
            updateTransaction(id: ${id} amount: ${amount} brand_id: ${brand}) {
                id
                amount
                category {
                    name
                    type
                }
                brand {
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

    updateCategory({id, name, type}) {
        return axios.post('/graphql', {query: `mutation {
            updateCategory(id: ${id} name: "${name}" type: "${type}") {
                id
                name
                type
            }
         }`});
    }
}