# AI Agent Guidelines for Control de Fichaje

This document provides comprehensive guidelines for AI agents (Claude, Gemini, ChatGPT, etc.) working with this Laravel application.

## Project Overview

**Control de fichaje** (Time Tracking Control) is a Laravel 11 web application built with advanced software architecture principles:

- **Architecture**: Domain-Driven Design (DDD) + Command and Query Responsibility Segregation (CQRS)
- **Command Bus**: `laravel-tactician` for handling application commands
- **Bounded Contexts**: `User`, `TimeTracking` (formerly RegistroHorario)
- **Layered Architecture**: Domain, Application, and Infrastructure layers

### Key Architectural Characteristics

The application maintains strict separation between:
- **Domain Entities**: Pure PHP objects containing business logic (persistence-ignorant)
- **Persistence Layer**: Repository pattern with Laravel's `DB` facade for manual data mapping
- **Eloquent Models**: Used selectively, not as the primary domain model approach

## Technology Stack

- **PHP**: 8.2.29
- **Laravel Framework**: v11
- **Laravel Sail**: v1 (Docker-based development)
- **Laravel Sanctum**: v4 (API authentication)
- **Laravel Pint**: v1 (code formatting)
- **PHPUnit**: v11 (testing)
- **Laravel Tactician**: Command bus implementation

## Development Environment

### Laravel Sail Commands

This project runs inside Laravel Sail's Docker containers. **ALL commands must be prefixed with `vendor/bin/sail`**.

```bash
# Start services
vendor/bin/sail up -d

# Stop services
vendor/bin/sail stop

# Run Artisan commands
vendor/bin/sail artisan migrate
vendor/bin/sail artisan make:controller ExampleController

# Composer commands
vendor/bin/sail composer install
vendor/bin/sail composer require package/name

# NPM commands
vendor/bin/sail npm run dev
vendor/bin/sail npm run build

# Run tests
vendor/bin/sail artisan test

# PHP scripts
vendor/bin/sail php script.php

# Open application in browser
vendor/bin/sail open
```

### Optional Alias
```bash
alias sail='sh $([ -f sail ] && echo sail || echo vendor/bin/sail)'
```

## Architecture Guidelines

### Directory Structure

```
app/
├── DDD/                          # Domain-Driven Design contexts
│   ├── TimeTracking/             # Time tracking bounded context (renamed from RegistroHorario)
│   │   ├── Application/          # Use cases (commands/queries)
│   │   │   ├── Command/          # ClockInCommand, ClockOutCommand
│   │   │   ├── Query/            # GetAccumulatedSecondsQuery, HasOpenTimeEntryQuery
│   │   │   └── Handler/          # Command/query handlers
│   │   ├── Domain/               # Entities, value objects, interfaces
│   │   │   ├── TimeEntry.php     # Main aggregate root
│   │   │   └── ValueObjects/     # TimeEntryId
│   │   └── Services/             # TimeTrackingService
│   ├── User/                     # User bounded context
│   │   ├── Application/
│   │   │   ├── Command/          # Commands (write operations)
│   │   │   └── Handler/          # Command/query handlers
│   │   ├── Domain/
│   │   │   ├── Entity/           # Domain entities
│   │   │   ├── ValueObjects/     # Value objects (Email, UserId, etc.)
│   │   │   ├── Interface/        # Repository interfaces
│   │   │   └── Exceptions/       # Domain exceptions
│   │   └── Infrastructure/
│   │       ├── Persistence/      # Repository implementations
│   │       └── Response/         # API response objects
│   └── Shared/                   # Shared components
│       ├── Domain/
│       │   ├── Bus/              # CommandBusInterface, QueryBusInterface
│       │   └── ValueObject/      # Base value objects
│       └── Infrastructure/
│           └── Bus/              # Laravel Tactician implementations
├── Http/
│   ├── Controllers/              # Thin HTTP controllers
│   └── Middleware/
├── Models/                       # Laravel Eloquent models (User, TimeEntry)
└── Providers/                    # Service providers (including DDDServiceProvider)
```

### Development Principles

#### 1. Domain-Driven Design (DDD)
- **Domain Layer**: Pure, persistence-ignorant entities and value objects
  - Business rules and invariants enforced here
  - No framework dependencies
  - Interfaces for repositories defined here

- **Application Layer**: Commands, queries, and their handlers
  - Orchestrates use cases
  - Coordinates domain objects
  - Transaction boundaries

- **Infrastructure Layer**: Framework-specific implementations
  - Repository implementations using `DB` facade
  - Event listeners
  - External service integrations

#### 2. Command and Query Responsibility Segregation (CQRS)
- **Commands**: Modify state, return void or simple acknowledgment
  - Example: `CreateUserCommand`, `ClockInCommand`, `ClockOutCommand`
  - Each command has a dedicated handler
  - Dispatched through command bus

- **Queries**: Read data, never modify state
  - Example: `GetUserByIdQuery`, `GetAccumulatedSecondsQuery`, `HasOpenTimeEntryQuery`
  - Return DTOs or response objects
  - Separate from commands

#### 3. Controller Guidelines
- Keep controllers **thin**
- Handle only HTTP concerns:
  - Request validation (via Form Requests)
  - Dispatching commands/queries
  - Formatting responses
- No business logic in controllers

#### 4. Repository Pattern
- Data access abstracted through interfaces (Domain layer)
- Concrete implementations in Infrastructure layer
- Manual mapping between database and domain entities
- Prefer `DB` facade over Eloquent for domain repositories

## PHP Code Standards

### Type Declarations
Always use explicit type declarations:

```php
// ✅ Good
protected function isAccessible(User $user, ?string $path = null): bool
{
    // Implementation
}

// ❌ Bad - missing return type
protected function isAccessible(User $user, $path = null)
{
    // Implementation
}
```

### Constructor Property Promotion
Use PHP 8 constructor property promotion:

```php
// ✅ Good
public function __construct(
    public UserRepositoryInterface $userRepository,
    public EventDispatcherInterface $eventDispatcher
) {}

// ❌ Bad - verbose
private UserRepositoryInterface $userRepository;

public function __construct(UserRepositoryInterface $userRepository)
{
    $this->userRepository = $userRepository;
}
```

### Control Structures
Always use curly braces, even for single-line statements:

```php
// ✅ Good
if ($condition) {
    return true;
}

// ❌ Bad
if ($condition)
    return true;
```

### Comments
- Prefer PHPDoc blocks over inline comments
- Avoid comments within code unless complexity demands it
- Add array shape type definitions in PHPDoc when appropriate

```php
/**
 * @param array{id: int, name: string, email: string} $userData
 * @return User
 */
public function createFromArray(array $userData): User
{
    // Implementation
}
```

### Enums
Use TitleCase for enum keys:

```php
enum Status
{
    case Pending;
    case InProgress;
    case Completed;
}
```

## Laravel Best Practices

### Artisan Commands
Always use artisan `make:` commands to create files:

```bash
# Controllers
vendor/bin/sail artisan make:controller ExampleController --no-interaction

# Models with factory and migration
vendor/bin/sail artisan make:model Example -mf --no-interaction

# Form Requests
vendor/bin/sail artisan make:request StoreUserRequest --no-interaction

# Tests
vendor/bin/sail artisan make:test UserTest --phpunit --no-interaction
vendor/bin/sail artisan make:test UserTest --unit --phpunit --no-interaction

# Generic PHP classes
vendor/bin/sail artisan make:class App/DDD/Domain/ValueObjects/Email --no-interaction
```

### Database Operations
- Use Eloquent relationships with return type hints
- Prevent N+1 queries with eager loading
- Prefer `Model::query()` over `DB::`
- For domain repositories: Use `DB` facade with manual mapping

```php
// ✅ Eloquent with relationships
public function user(): BelongsTo
{
    return $this->belongsTo(User::class);
}

// ✅ Eager loading to prevent N+1
$users = User::query()
    ->with('timeEntries')
    ->get();

// ✅ Get user with open time entry
$user = User::with('openTimeEntry')->find(1);
```

### Form Requests for Validation
Always create Form Request classes instead of inline validation:

```bash
vendor/bin/sail artisan make:request StoreTimeEntryRequest --no-interaction
```

```php
class StoreTimeEntryRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'userUuid' => 'required|string|exists:users,uuid',
            'entrada' => 'sometimes|date',
            'salida' => 'sometimes|date|after:entrada',
        ];
    }

    public function messages(): array
    {
        return [
            'userUuid.required' => 'El UUID del usuario es obligatorio.',
            'userUuid.exists' => 'El usuario no existe.',
            'salida.after' => 'La salida debe ser posterior a la entrada.',
        ];
    }
}
```

### Configuration
- Use environment variables **only** in config files
- Never call `env()` directly outside `/config` directory

```php
// ✅ Good
$appName = config('app.name');

// ❌ Bad
$appName = env('APP_NAME');
```

### URL Generation
Use named routes with the `route()` helper:

```php
// ✅ Good
return redirect()->route('users.show', ['user' => $user->id]);

// ❌ Bad
return redirect('/users/' . $user->id);
```

## Testing Guidelines

### Test Structure
- **Unit Tests** (`tests/Unit/`): Test isolated components
  - Domain and application layer logic
  - No database interactions
  - Mock dependencies

- **Feature Tests** (`tests/Feature/`): Test complete features
  - HTTP request through to database
  - Integration testing
  - Most tests should be feature tests

### Running Tests

```bash
# Run all tests
vendor/bin/sail artisan test

# Run specific test file
vendor/bin/sail artisan test tests/Feature/User/UserTest.php

# Filter by test name
vendor/bin/sail artisan test --filter=testUserCanBeCreated

# Run with coverage
vendor/bin/sail artisan test --coverage
```

### Test Data Creation
Use factories for model creation:

```php
// ✅ Good - using factory
$user = User::factory()->create([
    'email' => 'test@example.com',
]);

// Check for custom factory states
$admin = User::factory()->admin()->create();

// ❌ Bad - manual creation
$user = new User();
$user->name = 'Test User';
$user->email = 'test@example.com';
$user->save();
```

### Testing Best Practices
- Test happy paths, failure paths, and edge cases
- Run individual tests after making changes
- Use `$this->faker` or `fake()` helper (follow project convention)
- Never remove tests without approval
- After feature tests pass, offer to run full test suite

## Code Formatting

### Laravel Pint
Always run Pint before finalizing changes:

```bash
# Format modified files
vendor/bin/sail bin pint --dirty

# Format all files
vendor/bin/sail bin pint
```

**Do not** run `vendor/bin/sail bin pint --test` - just run pint to fix issues.

## Naming Conventions

### Descriptive Names
Use clear, descriptive names:

```php
// ✅ Good
public function isRegisteredForDiscounts(): bool

// ❌ Bad
public function discount(): bool
```

### Follow Project Conventions
Before creating files, check sibling files for:
- Naming patterns
- Structure
- Approach
- Coding style

## Common Issues

### Vite Manifest Error
If you see: `Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest`

Run:
```bash
vendor/bin/sail npm run build
# OR have user run:
vendor/bin/sail npm run dev
vendor/bin/sail composer run dev
```

## Important Rules for AI Agents

### What TO Do
✅ Follow existing code conventions strictly
✅ Use Laravel Sail for all commands
✅ Create Form Requests for validation
✅ Keep controllers thin
✅ Respect DDD boundaries and layers
✅ Use command bus for state modifications
✅ Write comprehensive tests (unit + feature)
✅ Run tests after changes
✅ Run Pint before finalizing
✅ Use explicit type declarations
✅ Check for existing components before creating new ones
✅ Use factories for test data

### What NOT to Do
❌ Execute commands without `vendor/bin/sail` prefix
❌ Create inline validation in controllers
❌ Put business logic in controllers
❌ Use `env()` outside config files
❌ Call `DB::` in application code (prefer repositories)
❌ Mix domain logic with infrastructure concerns
❌ Create empty `__construct()` methods
❌ Remove tests without approval
❌ Create documentation files unless explicitly requested
❌ Change dependencies without approval
❌ Create new base folders without approval
❌ Skip curly braces on single-line control structures
❌ Create verification scripts when tests exist

## Version Information

This is a **Laravel 11** application with specific considerations:

- Application configuration in `bootstrap/app.php`
- Middleware registration via `->withMiddleware()` in bootstrap
- Exception handling via `->withExceptions()` in bootstrap
- Console commands via `->withCommands()` in bootstrap
- Use `protected $casts = []` in models (Laravel 11 compatible)
- Rate limits configured in bootstrap or service providers

## Agent Communication

### Be Concise
- Focus on what's important
- Don't explain obvious details
- Get to the point

### No Unnecessary Scripts
- Don't create verification scripts when tests cover functionality
- Tests are the source of truth

### Proactive Behavior
- Check existing code patterns before creating new code
- Reuse components where possible
- Anticipate missing dependencies and requirements

## Quick Reference

### Command Cheat Sheet
```bash
# Development
vendor/bin/sail up -d
vendor/bin/sail artisan migrate
vendor/bin/sail artisan db:seed

# Testing
vendor/bin/sail artisan test
vendor/bin/sail artisan test --filter=testName

# Code Quality
vendor/bin/sail bin pint --dirty

# Asset Building
vendor/bin/sail npm run dev
vendor/bin/sail npm run build

# Composer
vendor/bin/sail composer install
vendor/bin/sail composer require package/name
```

### File Generation
```bash
# Controller
vendor/bin/sail artisan make:controller NameController --no-interaction

# Model + Migration + Factory
vendor/bin/sail artisan make:model Name -mf --no-interaction

# Form Request
vendor/bin/sail artisan make:request StoreNameRequest --no-interaction

# Test (Feature)
vendor/bin/sail artisan make:test NameTest --phpunit --no-interaction

# Test (Unit)
vendor/bin/sail artisan make:test NameTest --unit --phpunit --no-interaction

# Class
vendor/bin/sail artisan make:class Path/To/ClassName --no-interaction
```

---

## Current Architecture Status (Updated January 2026)

### Recent Major Changes
- **✅ Laravel 11 Upgrade**: Upgraded from Laravel 9 → 10 → 11
- **✅ TimeTracking Bounded Context**: Renamed from `RegistroHorario` to `TimeTracking`
- **✅ Database Optimization**: New `time_entries` table with optimized indexes
- **✅ Eloquent Relationships**: User ↔ TimeEntry relationships implemented
- **✅ CQRS Implementation**: Command/Query Bus with Laravel Tactician
- **✅ Timezone Configuration**: Europe/Madrid timezone properly configured

### Database Schema
```sql
-- Main tables
users (id, uuid, name, email, is_active, created_at, updated_at)
time_entries (id, user_id, entrada, salida, created_at, updated_at)

-- Optimized indexes on time_entries
idx_user_open_entries (user_id, salida)     -- Most critical for open entries
idx_entrada_date (entrada)                  -- Date range queries
idx_salida_date (salida)                    -- Exit date queries
idx_user_entrada (user_id, entrada)         -- User history queries
idx_date_range (entrada, salida)            -- Reports
idx_user_time_range (user_id, entrada, salida) -- Time calculations
```

### Key Architectural Decisions
1. **TimeEntry as User Aggregate**: TimeEntry is managed as part of User aggregate (not independent)
2. **Dual Model Approach**: Domain entities (DDD) + Eloquent models (ORM) coexist
3. **Manual Repository Mapping**: Domain repositories use `DB` facade with manual mapping
4. **Command Bus Pattern**: All state changes go through command bus
5. **Timezone Awareness**: All timestamps use Europe/Madrid timezone

### Current Bounded Contexts
- **User**: User management, authentication, user-related queries
- **TimeTracking**: Clock-in/out operations, time calculations, reporting
- **Shared**: Common interfaces, value objects, bus implementations

### Performance Optimizations
- **Database Indexes**: Optimized for common query patterns
- **Eloquent Relationships**: Proper hasMany/belongsTo for efficient queries
- **Eager Loading**: Prevent N+1 queries with `with()` clauses
- **Query Optimization**: Most frequent queries use dedicated indexes

## Container Commands

When making changes to entities, repositories, or domain logic, run:
```bash
vendor/bin/sail php artisan clear-compiled && vendor/bin/sail composer dump-autoload && vendor/bin/sail php artisan optimize
```

## Architecture Reference Documents
- `CHANGELOG_ARCHITECTURE.md`: Complete history of architectural changes
- `migrate_data.sql`: Data migration script (if needed)
- `plan.md`: Original architectural planning document

**Remember**: This application follows strict architectural patterns. When in doubt, examine existing implementations in the same bounded context before creating new code.

// Cargar usuario con todas sus entradas
$user = User::with('timeEntries')->find(1);

// Verificar si tiene entrada abierta
$openEntry = $user->openTimeEntry;

// Obtener todas las entradas del usuario
$allEntries = $user->timeEntries;

// Desde una entrada, obtener el usuario
$timeEntry = TimeEntry::with('user')->first();
$userName = $timeEntry->user->name;