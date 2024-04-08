# GraphQL

The entire backend is built on top of GraphQL. You can interact with the API using the [GraphQL Playground](/graphiql). The GraphQL Playground is a powerful tool that allows you to interact with the API, explore the schema, and test queries and mutations.

---

- [Endpoint](#endpoint)

<a name="endpoint"></a>
## GraphQL Endpoint

> {warning} Please note that you need to be authenticated to access the GraphQL endpoint by providing a valid `Authorization` header with a valid token. See the [Authentication](api-authentication) section for more information.

|Method| URI                             |Headers|
|:-|:--------------------------------|:-|
|POST| `/graphql` |Default|


> {info} GraphQL request example

```bash
curl --location 'https://finance-demo.saleem.dev/graphql' \
--header 'Authorization: Bearer API_TOKEN_HERE' \
--header 'Content-Type: application/json' \
--data '{"query":"query {\n    transactions(search: \"\" page: 0) {\n        data {\n            id\n            amount\n            created_at\n            note\n            brand {\n                id\n                name\n                category {\n                    name\n                    type\n                    color\n                }\n            }\n        }\n        paginatorInfo {\n            hasMorePages\n        }\n    }\n}","variables":{}}'
```
