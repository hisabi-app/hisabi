# GraphQL to REST API Migration - Continue Implementation

## Context
We have successfully migrated the **transaction index** endpoint from GraphQL to REST API using Laravel with CQRS and DDD patterns. This is a proof of concept that works end-to-end.

## What's Already Done

### Transaction Domain (Reference Implementation)
**Backend:**
- `app/Domains/Transaction/Models/Transaction.php` - Domain model
- `app/Domains/Transaction/Services/TransactionService.php` - Uses spatie/laravel-query-builder
- `app/Http/Queries/Transaction/GetTransactionsQuery/` - Query pattern with Query, Handler, Response
- `app/Http/Controllers/Api/V1/TransactionController.php` - REST controller
- Route: `GET /api/v1/transactions` in `routes/web.php` with `['auth']` middleware

**Frontend:**
- `resources/js/Api/transactions.js` - Updated `getTransactions()` to use REST API with `filter[search]` for query builder

**Key Points:**
- Routes are in `routes/web.php` (NOT api.php) with `middleware(['auth'])` 
- Uses `spatie/laravel-query-builder` for filtering, sorting, includes
- Frontend uses `fetch()` with CSRF token from cookies
- Filter syntax: `filter[search]=value`
- Response format matches GraphQL pagination structure

## Your Task

Migrate ALL remaining GraphQL queries and mutations to REST API following the same CQRS/DDD pattern.

## Remaining Endpoints to Migrate

### 1. Transactions (Complete the CRUD)
- ✅ `GET /api/v1/transactions` (index) - DONE
- ✅ `POST /api/v1/transactions` (create) - CreateTransactionCommand - DONE
- ✅ `PUT /api/v1/transactions/{id}` (update) - UpdateTransactionCommand - DONE
- ✅ `DELETE /api/v1/transactions/{id}` (delete) - DeleteTransactionCommand - DONE

### 2. Brands Domain
- ✅ `GET /api/v1/brands` (index) - GetBrandsQuery - DONE
- ✅ `GET /api/v1/brands/all` (all without pagination) - GetAllBrandsQuery - DONE
- ✅ `POST /api/v1/brands` (create) - CreateBrandCommand - DONE
- ✅ `PUT /api/v1/brands/{id}` (update) - UpdateBrandCommand - DONE
- ✅ `DELETE /api/v1/brands/{id}` (delete) - DeleteBrandCommand - DONE

### 3. Categories Domain
- ✅ `GET /api/v1/categories/all` (all without pagination) - GetAllCategoriesQuery - DONE
- ✅ `POST /api/v1/categories` (create) - CreateCategoryCommand - DONE
- ✅ `PUT /api/v1/categories/{id}` (update) - UpdateCategoryCommand - DONE
- ✅ `DELETE /api/v1/categories/{id}` (delete) - DeleteCategoryCommand - DONE

### 4. Budgets Domain
- ⬜ `GET /api/v1/budgets` (all) - GetBudgetsQuery

### 5. SMS Domain
- ✅ `GET /api/v1/sms` (index) - GetSmsQuery - DONE
- ✅ `POST /api/v1/sms` (create) - CreateSmsCommand
- ⬜ `PUT /api/v1/sms/{id}` (update) - UpdateSmsCommand
- ⬜ `DELETE /api/v1/sms/{id}` (delete) - DeleteSmsCommand

## Architecture Pattern to Follow

### Backend Structure for Each Domain

```
app/Domains/{DomainName}/
├── Models/{ModelName}.php              # Domain model
└── Services/{DomainName}Service.php    # Business logic

app/Http/Queries/{DomainName}/{QueryName}/
├── {QueryName}.php                     # Query object
├── {QueryName}Handler.php              # Handler
└── {QueryName}Response.php             # Response

app/Http/Commands/{DomainName}/{CommandName}/
├── {CommandName}.php                   # Command object
├── {CommandName}Handler.php            # Handler
└── {CommandName}Response.php           # Response

app/Http/Controllers/Api/V1/
└── {DomainName}Controller.php          # REST controller

app/Http/Requests/Api/V1/
└── {ActionName}{DomainName}Request.php # Form validation

tests/Feature/Api/V1/
└── {DomainName}ControllerTest.php      # REST API tests
```

### Example: Create Transaction Command

**Command:**
```php
// app/Http/Commands/Transaction/CreateTransactionCommand/CreateTransactionCommand.php
class CreateTransactionCommand
{
    public function __construct(
        public readonly array $data
    ) {}
}
```

**Handler:**
```php
// app/Http/Commands/Transaction/CreateTransactionCommand/CreateTransactionCommandHandler.php
class CreateTransactionCommandHandler
{
    public function __construct(
        private readonly TransactionService $transactionService
    ) {}

    public function handle(CreateTransactionCommand $command): CreateTransactionCommandResponse
    {
        return DB::transaction(function () use ($command) {
            $transaction = $this->transactionService->create($command->data);
            return new CreateTransactionCommandResponse($transaction);
        });
    }
}
```

**Response:**
```php
// app/Http/Commands/Transaction/CreateTransactionCommand/CreateTransactionCommandResponse.php
readonly class CreateTransactionCommandResponse
{
    public function __construct(
        private Transaction $transaction
    ) {}

    public function toResponse(): JsonResponse
    {
        return response()->json([
            'transaction' => $this->transaction->load('brand.category'),
        ], 201);
    }
}
```

**Controller:**
```php
public function store(CreateTransactionRequest $request): JsonResponse
{
    $command = new CreateTransactionCommand(
        data: $request->validated()
    );
    return $this->createTransactionCommandHandler->handle($command)->toResponse();
}
```

**Form Request:**
```php
// app/Http/Requests/Api/V1/CreateTransactionRequest.php
class CreateTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => 'required|numeric|min:0',
            'brand_id' => 'required|integer|exists:brands,id',
            'created_at' => 'required|date',
            'note' => 'nullable|string|max:1000',
        ];
    }
}
```

### Frontend Pattern

Update each API file in `resources/js/Api/` to use REST endpoints:

```javascript
const getCsrfToken = () => {
    const token = document.querySelector('meta[name="csrf-token"]');
    return token ? token.getAttribute('content') : '';
};

export const createResource = async (data) => {
    const response = await fetch(`/api/v1/resources`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': getCsrfToken(),
            'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin',
        body: JSON.stringify(data)
    });

    if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
    }

    return await response.json();
}
```

### Testing Pattern

Create REST API tests in `tests/Feature/Api/V1/{DomainName}ControllerTest.php`:

```php
<?php

namespace Tests\Feature\Api\V1;

use App\Domains\{DomainName}\Models\{ModelName};
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class {DomainName}ControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_it_requires_authentication(): void
    {
        $response = $this->postJson('/api/v1/resources', []);
        $response->assertStatus(401);
    }

    public function test_it_creates_a_resource(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/resources', [
                'field' => 'value'
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'data' => ['field' => 'value']
            ]);
    }

    public function test_it_validates_required_fields(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/resources', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['field']);
    }
}
```

## Implementation Requirements

1. **DO NOT add comments** - Clean code only
2. **Use spatie/laravel-query-builder** for all index/list queries
3. **Use DB::transaction()** for write operations in commands
4. **Add Form Requests** for validation on all write operations
5. **Routes go in routes/web.php** with `middleware(['auth'])`
6. **Response format** should match what frontend expects (check existing components)
7. **Domain Services** should use query builder, not Eloquent directly
8. **All routes** follow RESTful conventions: `/api/v1/{resource}` pattern
9. **Frontend updates** - Update ALL corresponding files in `resources/js/Api/` and make sure the calls to the api from fronend are updated as well
10. **Test updates** - Create/update tests in `tests/Feature/Api/V1/` using REST API patterns
11. **Test end-to-end** - Make sure frontend pages work after migration
12. **Remove the query/mutation** from graphql and the tests in the tests folder if any

## Reference Files to Check

- `graphql/schema.graphql` - See all current GraphQL queries/mutations
- `resources/js/Api/` - All API client files that need updating
- `resources/js/pages/` - Frontend pages that use the APIs
- `app/Models/` - Existing models to move to Domains

## Success Criteria

- ✅ All GraphQL queries and mutations have REST equivalents
- ✅ All frontend API calls updated to use REST
- ✅ All pages load and function correctly
- ✅ CRUD operations work end-to-end
- ✅ Follows CQRS/DDD architecture consistently
- ✅ Uses laravel-query-builder for filtering/sorting
- ✅ Proper authentication with session (not tokens)
- ✅ No linter errors
- ✅ Removed from graphql schema and its graphql tests

## Start With

Begin with completing the Transaction CRUD operations, then move to Brands, Categories, Budgets, and SMS in that order.

For each domain:
1. Create Domain structure (Models + Services)
2. Create Queries for read operations
3. Create Commands for write operations
4. Create Controller with all CRUD methods
5. Create Form Requests for validation
6. Add routes to routes/web.php
7. Update frontend API file
8. Create/update REST API tests in tests/Feature/Api/V1/
9. Test end-to-end in browser
10. Remove the graphql query/command from schema.graphql

