# API Authentication

---

- [Login](#login)

<a name="login"></a>
## Login


### Endpoint

|Method| URI                               |Headers|
|:-|:----------------------------------|:-|
|POST| `/api/login` |Default|

### Body Params

```json
{
    "email": "string|email",
    "password": "string",
    "device_name": "string"
}
```

> {info} Login request example

```json
{
    "email": "admin@test.com",
    "password": "admin123",
    "device_name": "iPhone 15 Pro Max"
}
```

curl example

```bash
curl --location 'https://finance-demo.saleem.dev/api/login' \
--header 'Accept: application/json' \
--header 'Content-Type: application/json' \
--data-raw '{
    "email": "admin@test.com",
    "password": "admin123",
    "device_name": "iPhone 15 Pro Max"
}'
```


> {danger} Error Response

```json
{
    "message": "The provided credentials are incorrect.",
    "errors": {
        "email": [
            "The provided credentials are incorrect."
        ]
    }
}
```

> {success} Success Response (the token)

```text
string
```

You can use this token to authenticate your upcoming requests. You can pass this token in the `Authorization` header as a `Bearer` token.
