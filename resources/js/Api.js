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
            updateTransaction(id: ${id} amount: ${amount} brand: ${brand}) {
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

    updateCategory({id, name, type}) {
        console.log(name)
        return axios.post('/graphql', {query: `mutation {
            updateCategory(id: ${id} name: "${name}" type: "${type}") {
                id
                name
                type
            }
         }`});
    }
}