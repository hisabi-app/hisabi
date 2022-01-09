export default class Api {
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
                        name
                    }
                }
                paginatorInfo {
                    hasMorePages
                }
            }
         }`});
    }
}