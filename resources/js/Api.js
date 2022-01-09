export default class Api {
    getBrands() {
        return axios.post('/graphql', {query: `query {
            brands {
                id
                name
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
}