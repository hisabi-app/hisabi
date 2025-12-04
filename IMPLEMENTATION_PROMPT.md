# Task: Implement Multi-Account System for Laravel Finance App (Hisabi)

## Context
You are working on a Laravel 12 finance tracking application that currently has a single-user design with NO tenant isolation. All models (Category, Brand, Transaction, SMS, Budget) are global and not scoped to users.

## Tech Stack
- **Backend**: Laravel 12, PHP 8.2+, MySQL
- **Frontend**: React 19 + Inertia.js 2.0, TypeScript 5.7, Tailwind CSS 4
- **Patterns**: Domain-Driven Design, CQRS (Commands/Queries)
- **Auth**: Laravel Sanctum
- **Testing**: PHPUnit/Pest

## Current Database Schema
```
Users (id, name, email, password)
Categories (id, name, type[INCOME|EXPENSES|SAVINGS|INVESTMENT], color, icon)
Brands (id, name, category_id)
Transactions (id, amount, brand_id, note, meta)
SMS (id, body, transaction_id, meta)
Budgets (id, name, amount, start_at, end_at, saving, period, reoccurrence)
budget_category (pivot: budget_id, category_id)
```

**Current Relationships:**
- Category → hasMany(Brand)
- Brand → hasMany(Transaction)
- Transaction → belongsTo(Brand)
- SMS → belongsTo(Transaction)
- Budget → belongsToMany(Category)

## Requirements

### 1. Implement Multi-Tenant Architecture
**Tenant Name**: "Household" (not workspace/family/organization)

**New Hierarchy:**
```
User ←→ Household (many-to-many with pivot for future role support)
    ↓
Household → Account (one-to-many) [Checking, Savings, Credit Card, Cash, etc.]
Household → Category (one-to-many) [Tenant-isolated]
Household → Brand (one-to-many) [Tenant-isolated]
    ↓
Account → Transaction (one-to-many)
Account → Budget (one-to-many)
Account → SMS (one-to-many)
```

**Key Constraint**: For MVP, users can have ONLY ONE household. This is to detach data from User model to enable future features (invitations, sharing).

### 2. Transaction Type Evolution
Keep current category-based logic BUT add transfer support:

**Transaction types:**
- `income` - Uses category with type=INCOME
- `expense` - Uses category with type=EXPENSES/SAVINGS/INVESTMENT
- `transfer` - Transfer between accounts (no category required)

**Transfer Implementation:**
```php
Transaction {
    type: enum('income', 'expense', 'transfer')
    account_id: FK (primary account)
    category_id: FK (nullable - required for income/expense only)
    brand_id: FK (nullable)
    amount: decimal

    // Transfer-specific fields
    transfer_account_id: FK (nullable - destination account)
    linked_transaction_id: FK (nullable - paired transaction ID)
}
```

**Transfer Logic:**
- Creates paired transactions in both accounts
- Source account: negative amount
- Destination account: positive amount
- Both linked via `linked_transaction_id`

### 3. Account Types
```php
const CHECKING = 'checking';
const SAVINGS = 'savings';
const CREDIT_CARD = 'credit_card';
const INVESTMENT = 'investment';
const CASH = 'cash';
const LOAN = 'loan';
```

### 4. Authentication Flow Changes
**Current**: Registration disabled
**New**:
1. Enable user registration
2. After registration, redirect to "Create Household" page
3. User must create household before accessing the app
4. Store household selection in session/context

## Implementation Checklist

### Database Layer
- [ ] Create `households` migration (id, name, slug, settings JSON, timestamps)
- [ ] Create `household_user` pivot migration (household_id, user_id, role, timestamps)
- [ ] Create `accounts` migration (id, household_id, name, type, currency, initial_balance, current_balance, timestamps)
- [ ] Add migrations to add `household_id` to: categories, brands
- [ ] Add migrations to add `account_id` to: transactions, budgets, sms
- [ ] Add migrations for transaction transfer fields: `type` enum, `transfer_account_id`, `linked_transaction_id`
- [ ] Create Household model with relationships
- [ ] Create Account model with relationships
- [ ] Update all existing models to include household/account relationships

### Models & Scoping
- [ ] Add global scopes to auto-filter by household: Category, Brand
- [ ] Add global scopes to auto-filter by account: Transaction, Budget, SMS
- [ ] Implement `HouseholdContext` service/trait for getting current household
- [ ] Add validation: Ensure user can only access their household data
- [ ] Update factories for all models to include household/account

### Services & Business Logic
- [ ] Update `TransactionService` to handle transfers (create paired transactions)
- [ ] Update `BrandService` to scope by household
- [ ] Update `CategoryService` (if exists) to scope by household
- [ ] Create `AccountService` for account management
- [ ] Create `HouseholdService` for household creation/management

### API & Controllers
- [ ] Create `HouseholdController` (create, show, update)
- [ ] Create `AccountController` (index, create, update, delete, show balance)
- [ ] Update `TransactionController` to support transfer type
- [ ] Update `BrandController` to scope by household
- [ ] Update `CategoryController` to scope by household
- [ ] Update all CQRS Queries to include household/account scoping
- [ ] Update all CQRS Commands to include household/account assignment

### Frontend (React + Inertia)
- [ ] Create `CreateHousehold.tsx` page (post-registration)
- [ ] Create `AccountSelector.tsx` component (switch active account)
- [ ] Create `Accounts/Index.tsx` page (list all accounts with balances)
- [ ] Create `Accounts/Create.tsx` page (create new account)
- [ ] Update `Transactions/Create.tsx` to support transfer type
- [ ] Add account dropdown to transaction forms
- [ ] Update dashboard to show per-account balances
- [ ] Update reports/charts to filter by account
- [ ] Add household name display in navbar/header

### Routes
- [ ] Add registration routes (POST /register)
- [ ] Add `GET/POST /household/create` (protected, must not have household)
- [ ] Add household routes under `/households`
- [ ] Add account routes under `/accounts`
- [ ] Add middleware to ensure user has household before accessing app

### Middleware
- [ ] Create `EnsureUserHasHousehold` middleware
- [ ] Apply middleware to all protected routes except `/household/create`
- [ ] Store current household in session/request

### Testing
- [ ] Update all existing tests to create household + account context
- [ ] Test household isolation (User A cannot see User B's data)
- [ ] Test account isolation (Account 1 data separate from Account 2)
- [ ] Test transfer creation (paired transactions)
- [ ] Test transfer deletion (cascade to paired transaction)
- [ ] Test category/brand scoping by household
- [ ] Add feature tests for household creation flow
- [ ] Add feature tests for account CRUD
- [ ] Run full test suite and ensure all pass

### Data Migration Strategy
- [ ] Create seeder to assign existing data to default household + account
- [ ] Handle empty database (fresh install) vs existing data
- [ ] Document migration steps for production

## Important Constraints
1. **One Household Per User** (MVP): Users can only belong to ONE household initially
2. **Tenant Isolation**: Categories and Brands MUST be household-scoped (not shared globally)
3. **Backward Compatibility**: Existing category type logic must work (INCOME, EXPENSES, etc.)
4. **Cascade Deletes**: Maintain existing cascade behavior (Brand delete → Transaction delete, etc.)
5. **Testing**: All tests must pass before completion

## Git Workflow
- Branch: `claude/implement-multi-account-system-011CV4dVar7tKUU3hW6XuuGR`
- Commit messages: Clear, descriptive
- Push when complete: `git push -u origin claude/implement-multi-account-system-011CV4dVar7tKUU3hW6XuuGR`

## Deliverables
1. ✅ All migrations created and tested
2. ✅ All models updated with relationships and scopes
3. ✅ All controllers/services updated
4. ✅ Registration flow + household creation UI
5. ✅ Account management UI
6. ✅ Transfer functionality in transactions
7. ✅ All tests passing
8. ✅ Code committed and pushed

## Success Criteria
- User can register → create household → create accounts → create transactions
- Transfers work between accounts
- All data properly scoped by household
- No data leakage between households
- All existing features work within new architecture
- Test suite passes 100%
