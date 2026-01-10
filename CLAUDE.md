<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to enhance the user's satisfaction building Laravel applications.

## Foundational Context
This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.2.29
- laravel/framework (LARAVEL) - v10
- laravel/prompts (PROMPTS) - v0
- laravel/sanctum (SANCTUM) - v3
- laravel/mcp (MCP) - v0
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- phpunit/phpunit (PHPUNIT) - v10

## Conventions
- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts
- Do not create verification scripts or tinker when tests cover that functionality and prove it works. Unit and feature tests are more important.

## Application Structure & Architecture
- Stick to existing directory structure - don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling
- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `vendor/bin/sail npm run build`, `vendor/bin/sail npm run dev`, or `vendor/bin/sail composer run dev`. Ask them.

## Replies
- Be concise in your explanations - focus on what's important rather than explaining obvious details.

## Documentation Files
- You must only create documentation files if explicitly requested by the user.


=== boost rules ===

## Laravel Boost
- Laravel Boost is an MCP server that comes with powerful tools designed specifically for this application. Use them.

## Artisan
- Use the `list-artisan-commands` tool when you need to call an Artisan command to double check the available parameters.

## URLs
- Whenever you share a project URL with the user you should use the `get-absolute-url` tool to ensure you're using the correct scheme, domain / IP, and port.

## Tinker / Debugging
- You should use the `tinker` tool when you need to execute PHP to debug code or query Eloquent models directly.
- Use the `database-query` tool when you only need to read from the database.

## Reading Browser Logs With the `browser-logs` Tool
- You can read browser logs, errors, and exceptions using the `browser-logs` tool from Boost.
- Only recent browser logs will be useful - ignore old logs.

## Searching Documentation (Critically Important)
- Boost comes with a powerful `search-docs` tool you should use before any other approaches. This tool automatically passes a list of installed packages and their versions to the remote Boost API, so it returns only version-specific documentation specific for the user's circumstance. You should pass an array of packages to filter on if you know you need docs for particular packages.
- The 'search-docs' tool is perfect for all Laravel related packages, including Laravel, Inertia, Livewire, Filament, Tailwind, Pest, Nova, Nightwatch, etc.
- You must use this tool to search for Laravel-ecosystem documentation before falling back to other approaches.
- Search the documentation before making code changes to ensure we are taking the correct approach.
- Use multiple, broad, simple, topic based queries to start. For example: `['rate limiting', 'routing rate limiting', 'routing']`.
- Do not add package names to queries - package information is already shared. For example, use `test resource table`, not `filament 4 test resource table`.

### Available Search Syntax
- You can and should pass multiple queries at once. The most relevant results will be returned first.

1. Simple Word Searches with auto-stemming - query=authentication - finds 'authenticate' and 'auth'
2. Multiple Words (AND Logic) - query=rate limit - finds knowledge containing both "rate" AND "limit"
3. Quoted Phrases (Exact Position) - query="infinite scroll" - Words must be adjacent and in that order
4. Mixed Queries - query=middleware "rate limit" - "middleware" AND exact phrase "rate limit"
5. Multiple Queries - queries=["authentication", "middleware"] - ANY of these terms


=== php rules ===

## PHP

- Always use curly braces for control structures, even if it has one line.

### Constructors
- Use PHP 8 constructor property promotion in `__construct()`.
    - <code-snippet>public function __construct(public GitHub $github) { }</code-snippet>
- Do not allow empty `__construct()` methods with zero parameters.

### Type Declarations
- Always use explicit return type declarations for methods and functions.
- Use appropriate PHP type hints for method parameters.

<code-snippet name="Explicit Return Types and Method Params" lang="php">
protected function isAccessible(User $user, ?string $path = null): bool
{
    ...
}
</code-snippet>

## Comments
- Prefer PHPDoc blocks over comments. Never use comments within the code itself unless there is something _very_ complex going on.

## PHPDoc Blocks
- Add useful array shape type definitions for arrays when appropriate.

## Enums
- Typically, keys in an Enum should be TitleCase. For example: `FavoritePerson`, `BestLake`, `Monthly`.


=== sail rules ===

## Laravel Sail

- This project runs inside Laravel Sail's Docker containers. You MUST execute all commands through Sail.
- Start services using `vendor/bin/sail up -d` and stop them with `vendor/bin/sail stop`.
- Open the application in the browser by running `vendor/bin/sail open`.
- Always prefix PHP, Artisan, Composer, and Node commands** with `vendor/bin/sail`. Examples:
- Run Artisan Commands: `vendor/bin/sail artisan migrate`
- Install Composer packages: `vendor/bin/sail composer install`
- Execute node commands: `vendor/bin/sail npm run dev`
- Execute PHP scripts: `vendor/bin/sail php [script]`
- View all available Sail commands by running `vendor/bin/sail` without arguments.


=== laravel/core rules ===

## Do Things the Laravel Way

- Use `vendor/bin/sail artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using the `list-artisan-commands` tool.
- If you're creating a generic PHP class, use `vendor/bin/sail artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Database
- Always use proper Eloquent relationship methods with return type hints. Prefer relationship methods over raw queries or manual joins.
- Use Eloquent models and relationships before suggesting raw database queries
- Avoid `DB::`; prefer `Model::query()`. Generate code that leverages Laravel's ORM capabilities rather than bypassing them.
- Generate code that prevents N+1 query problems by using eager loading.
- Use Laravel's query builder for very complex database operations.

### Model Creation
- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `list-artisan-commands` to check the available options to `vendor/bin/sail artisan make:model`.

### APIs & Eloquent Resources
- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

### Controllers & Validation
- Always create Form Request classes for validation rather than inline validation in controllers. Include both validation rules and custom error messages.
- Check sibling Form Requests to see if the application uses array or string based validation rules.

### Queues
- Use queued jobs for time-consuming operations with the `ShouldQueue` interface.

### Authentication & Authorization
- Use Laravel's built-in authentication and authorization features (gates, policies, Sanctum, etc.).

### URL Generation
- When generating links to other pages, prefer named routes and the `route()` function.

### Configuration
- Use environment variables only in configuration files - never use the `env()` function directly outside of config files. Always use `config('app.name')`, not `env('APP_NAME')`.

### Testing
- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `vendor/bin/sail artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

### Vite Error
- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `vendor/bin/sail npm run build` or ask the user to run `vendor/bin/sail npm run dev` or `vendor/bin/sail composer run dev`.


=== laravel/v10 rules ===

## Laravel 10

- Use the `search-docs` tool to get version specific documentation.
- Middleware typically live in `app/Http/Middleware/` and service providers in `app/Providers/`.
- Laravel 10 has a `bootstrap/app.php` file that creates the application instance and binds kernel contracts, but does not use it for application configuration like Laravel 11:
    - Middleware registration is in `app/Http/Kernel.php`
    - Exception handling is in `app/Exceptions/Handler.php`
    - Console commands and schedule registration is in `app/Console/Kernel.php`
    - Rate limits likely exist in `RouteServiceProvider` or `app/Http/Kernel.php`
- When using Eloquent model casts, you must use `protected $casts = [];` and not the `casts()` method. The `casts()` method isn't available on models in Laravel 10.


=== pint/core rules ===

## Laravel Pint Code Formatter

- You must run `vendor/bin/sail bin pint --dirty` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/sail bin pint --test`, simply run `vendor/bin/sail bin pint` to fix any formatting issues.


=== phpunit/core rules ===

## PHPUnit Core

- This application uses PHPUnit for testing. All tests must be written as PHPUnit classes. Use `vendor/bin/sail artisan make:test --phpunit {name}` to create a new test.
- If you see a test using "Pest", convert it to PHPUnit.
- Every time a test has been updated, run that singular test.
- When the tests relating to your feature are passing, ask the user if they would like to also run the entire test suite to make sure everything is still passing.
- Tests should test all of the happy paths, failure paths, and weird paths.
- You must not remove any tests or test files from the tests directory without approval. These are not temporary or helper files, these are core to the application.

### Running Tests
- Run the minimal number of tests, using an appropriate filter, before finalizing.
- To run all tests: `vendor/bin/sail artisan test`.
- To run all tests in a file: `vendor/bin/sail artisan test tests/Feature/ExampleTest.php`.
- To filter on a particular test name: `vendor/bin/sail artisan test --filter=testName` (recommended after making a change to a related file).
</laravel-boost-guidelines>

## Convenciones del Proyecto TimeTrack

### Arquitectura DDD
- Los nuevos usuarios se crean con `is_active = false` por defecto
- El campo `is_admin` (boolean) determina si un usuario es administrador, NO usar `remember_token`
- Al llamar a `User::fromPrimitives()`, el parámetro `isAdmin` es obligatorio (bool), no pasar null

### Frontend (Materialize CSS)
- Usar Materialize CSS, NO Tailwind
- Switch con colores personalizados: usar estilos inline para `.lever` y `.lever:after`
- Navbar sticky: usar clase `navbar-fixed`
- Formularios responsive: usar `col s12 l6 offset-l3`

### Tests
- Al modificar la firma de `User::fromPrimitives()`, actualizar TODOS los tests que lo usan
- Verificar que los tests reflejan el comportamiento actual (ej: redirecciones a `bienvenido` en lugar de `home`)
- El contador de tests usa `TestCounter::count()` que escanea archivos `*Test.php`

### Rutas y Redirecciones
- Post-registro redirige a `/bienvenido`, no a `/registro-horario`
- Usuarios inactivos que intentan acceder a Fichar son redirigidos a `/bienvenido`
- La ruta `home` ya no existe, usar `bienvenido`

### Limpieza de Código
- Buscar y eliminar archivos huérfanos (Value Objects, Exceptions, etc. no usados)
- Eliminar imports no utilizados
- Al cambiar arquitectura (ej: remember_token → is_admin), buscar TODAS las referencias

### Documentación de Ramas
- Al finalizar una funcionalidad, documentar automáticamente en `.claude/code/{nombre-rama}.md`
- Si el archivo existe, actualizarlo con los nuevos cambios
- Incluir: descripción, archivos creados/modificados, comandos, estructura BD, tests

Documentacion añadida a mano

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


# Changelog de Arquitectura - Control de Fichaje

## 📋 Resumen de Cambios Arquitectónicos

Este documento registra todos los cambios importantes realizados en la arquitectura del sistema de control de fichaje, incluyendo comandos útiles para pruebas y verificación.

---

## 🏗️ Cambios Principales Implementados

### 1. **Migración de RegistroHorario → TimeTracking**
- **Fecha**: Enero 2026
- **Objetivo**: Unificar naming y mejorar arquitectura DDD
- **Estado**: ✅ Completado

#### Cambios Realizados:
- ✅ Eliminado directorio obsoleto `app/DDD/RegistroHorario/`
- ✅ Creado bounded context `app/DDD/TimeTracking/`
- ✅ Renombrado modelo `RegistroHorario.php` → `TimeEntry.php`
- ✅ Creada tabla `time_entries` (reemplaza `registro_horarios`)
- ✅ Actualizadas todas las referencias de código

#### Archivos Afectados:
```
- app/Models/RegistroHorario.php → app/Models/TimeEntry.php
- app/DDD/RegistroHorario/ → ELIMINADO
- app/DDD/TimeTracking/ → CREADO
- database/migrations/2026_01_01_132732_create_time_entries_table.php → CREADO
- app/DDD/User/Infrastructure/Persistence/Eloquent/EloquentUserRepository.php
- app/DDD/User/Application/Handler/GetUserDailyRegistrosQueryHandler.php
- tests/Feature/RegistroHorario/RegistroHorarioTest.php
```

### 2. **Implementación de Relaciones Eloquent**
- **Fecha**: Enero 2026
- **Objetivo**: Mejorar eficiencia de consultas ORM
- **Estado**: ✅ Completado

#### Relaciones Agregadas:
```php
// User Model
public function timeEntries(): HasMany
public function openTimeEntry(): HasOne

// TimeEntry Model  
public function user(): BelongsTo
```

### 3. **Arquitectura CQRS Mejorada**
- **Fecha**: Diciembre 2025
- **Objetivo**: Implementar Command/Query Bus consistente
- **Estado**: ✅ Completado

#### Componentes Creados:
- `CommandBusInterface` y `QueryBusInterface`
- `LaravelTacticianCommandBus` y `LaravelTacticianQueryBus`
- `DDDServiceProvider` para registro de handlers
- Refactorización de controladores para usar buses

### 4. **Optimización de Índices de Base de Datos**
- **Fecha**: Enero 2026
- **Objetivo**: Optimizar rendimiento de consultas frecuentes
- **Estado**: ✅ Completado

#### Índices Implementados:
- `idx_user_open_entries` (user_id, salida): Para buscar entradas abiertas por usuario
- `idx_entrada_date` (entrada): Para consultas por fecha de entrada
- `idx_salida_date` (salida): Para consultas por fecha de salida
- `idx_user_entrada` (user_id, entrada): Para consultas de usuario por fecha
- `idx_date_range` (entrada, salida): Para reportes de rangos de fecha
- `idx_user_time_range` (user_id, entrada, salida): Para tiempo trabajado por usuario

#### Beneficios de Rendimiento:
- ✅ Consultas de "entrada abierta" optimizadas (1 row examined)
- ✅ Reportes diarios/mensuales más rápidos
- ✅ Consultas de tiempo trabajado eficientes
- ✅ Prevención de full table scans

---

## 🧪 Comandos de Pruebas y Verificación

### **Tests Unitarios**
```bash
# Ejecutar todos los tests unitarios
vendor/bin/sail artisan test --testsuite=Unit

# Test específico por filtro
vendor/bin/sail artisan test --filter="it_fichas_entrada_successfully"

# Tests con coverage
vendor/bin/sail artisan test --coverage
```

### **Tests de Feature**
```bash
# Todos los tests de feature
vendor/bin/sail artisan test --testsuite=Feature

# Test específico de RegistroHorario
vendor/bin/sail artisan test tests/Feature/RegistroHorario/RegistroHorarioTest.php

# Test de integración User-TimeEntry
vendor/bin/sail artisan test tests/Feature/Integration/UserRegistroHorarioIntegrationTest.php
```

### **Verificación de Base de Datos**
```bash
# Verificar tablas existentes
vendor/bin/sail artisan tinker --execute="
use Illuminate\Support\Facades\DB;
\$tables = DB::select('SHOW TABLES');
foreach(\$tables as \$table) {
    \$tableName = array_values((array)\$table)[0];
    if(strpos(\$tableName, 'entries') !== false || strpos(\$tableName, 'registro') !== false) {
        echo \$tableName . ' - Registros: ' . DB::table(\$tableName)->count() . PHP_EOL;
    }
}
"

# Verificar índices optimizados
vendor/bin/sail artisan tinker --execute="
use Illuminate\Support\Facades\DB;
echo 'Índices en time_entries:' . PHP_EOL;
\$indexes = DB::select('SHOW INDEX FROM time_entries');
foreach(\$indexes as \$index) {
    echo '- ' . \$index->Key_name . ' (' . \$index->Column_name . ')' . PHP_EOL;
}
"

# Verificar rendimiento de consultas críticas
vendor/bin/sail artisan tinker --execute="
use Illuminate\Support\Facades\DB;
echo 'EXPLAIN consulta entrada abierta:' . PHP_EOL;
\$explain = DB::select('EXPLAIN SELECT * FROM time_entries WHERE user_id = 1 AND salida IS NULL');
foreach(\$explain as \$row) {
    echo 'Key: ' . (\$row->key ?: 'NINGUNO') . ' | Rows: ' . \$row->rows . PHP_EOL;
}
"

# Verificar relaciones Eloquent
vendor/bin/sail artisan tinker --execute="
use App\Models\User;
\$user = User::with('timeEntries')->first();
echo 'Usuario: ' . \$user->name . PHP_EOL;
echo 'Time Entries: ' . \$user->timeEntries->count() . PHP_EOL;
echo 'Entrada Abierta: ' . (\$user->openTimeEntry ? 'SÍ' : 'NO') . PHP_EOL;
"
```

### **Verificación de Arquitectura**
```bash
# Verificar que no hay referencias obsoletas
grep -r "RegistroHorario" app/ --exclude-dir=vendor
grep -r "registro_horarios" app/ --exclude-dir=vendor

# Verificar estructura de bounded contexts
find app/DDD -type d -name "*" | sort

# Verificar handlers registrados
vendor/bin/sail artisan route:list | grep registro
```

---

## 🔧 Comandos de Mantenimiento

### **Limpieza de Cache**
```bash
# Limpiar caches después de cambios arquitectónicos
vendor/bin/sail artisan clear-compiled
vendor/bin/sail composer dump-autoload
vendor/bin/sail artisan optimize
```

### **Migraciones**
```bash
# Ejecutar migraciones
vendor/bin/sail artisan migrate

# Rollback si es necesario
vendor/bin/sail artisan migrate:rollback

# Estado de migraciones
vendor/bin/sail artisan migrate:status
```

### **Formateo de Código**
```bash
# Formatear código modificado
vendor/bin/sail bin pint --dirty

# Formatear todo el código
vendor/bin/sail bin pint
```

---

## 📊 Estado Actual de la Arquitectura

### **✅ Completado**
- [x] Bounded Context TimeTracking implementado
- [x] Tabla time_entries creada y funcionando
- [x] Modelo TimeEntry con relaciones Eloquent
- [x] Tests unitarios pasando (21 tests)
- [x] CQRS con Command/Query Bus
- [x] Eliminación de código obsoleto
- [x] Timezone corregido (Europe/Madrid)
- [x] UI collapsible para "Todos los Fichajes"

### **⚠️ Pendiente (Opcional)**
- [ ] Migración manual de datos históricos (script `migrate_data.sql` disponible)
- [ ] Eliminación de tabla `registro_horarios` (cuando se confirme migración)
- [ ] Refactoring completo a agregados independientes (futuro)

---

## 🎯 Verificaciones Críticas

### **Antes de Deploy**
1. ✅ Ejecutar todos los tests: `vendor/bin/sail artisan test`
2. ✅ Verificar linting: `vendor/bin/sail bin pint --dirty`
3. ✅ Confirmar migraciones: `vendor/bin/sail artisan migrate:status`
4. ✅ Probar funcionalidad crítica: clock-in/clock-out
5. ✅ Verificar relaciones Eloquent funcionando

### **Después de Deploy**
1. Verificar que `time_entries` recibe datos nuevos
2. Confirmar que `registro_horarios` permanece vacía
3. Ejecutar script de migración de datos si es necesario
4. Monitorear logs por errores relacionados con TimeEntry

---

## 📝 Notas Importantes

### **Separación de Responsabilidades**
- **Entidades de Dominio** (`app/DDD/*/Domain/`): Lógica de negocio pura
- **Modelos Eloquent** (`app/Models/`): Acceso a datos y relaciones ORM
- **Repositorios** (`app/DDD/*/Infrastructure/`): Mapeo entre dominio y persistencia
- **Servicios de Aplicación**: Orquestación de casos de uso

### **Convenciones de Naming**
- **Bounded Context**: `TimeTracking` (inglés, PascalCase)
- **Entidad de Dominio**: `TimeEntry` (inglés, PascalCase)  
- **Tabla**: `time_entries` (inglés, snake_case, plural)
- **Modelo Eloquent**: `TimeEntry` (inglés, PascalCase, singular)

### **Comandos de Emergencia**
```bash
# Si algo falla, rollback rápido
vendor/bin/sail artisan migrate:rollback --step=1

# Restaurar autoload
vendor/bin/sail composer dump-autoload

# Limpiar todo el cache
vendor/bin/sail artisan optimize:clear
```

---

**Última actualización**: Enero 2026  
**Versión Laravel**: 11.x  
**Versión PHP**: 8.2.29



# Laravel Telescope

## Instalación

Laravel Telescope v5.16.0 está instalado en este proyecto.

## Acceso al Dashboard

El dashboard de Telescope está disponible en:

```
http://localhost/telescope
```

## Configuración

### Archivo de Configuración

La configuración principal se encuentra en `config/telescope.php`.

### Variables de Entorno

- `TELESCOPE_ENABLED`: Habilita/deshabilita Telescope (default: true)
- `TELESCOPE_DOMAIN`: Subdominio opcional para Telescope
- `TELESCOPE_PATH`: Ruta del dashboard (default: 'telescope')

### Service Provider

El service provider está ubicado en `app/Providers/TelescopeServiceProvider.php`.

#### Filtros Configurados

En entorno local, Telescope registra todo. En otros entornos, solo registra:
- Excepciones reportables
- Peticiones fallidas
- Jobs fallidos
- Tareas programadas
- Entradas con tags monitoreados

#### Datos Sensibles Ocultos

En entornos no locales, se ocultan:
- Parámetros: `_token`
- Headers: `cookie`, `x-csrf-token`, `x-xsrf-token`

## Autorización

### Entorno Local

En el entorno `local`, el acceso es completamente libre.

### Entornos No Locales

El acceso está controlado por el gate `viewTelescope` en `TelescopeServiceProvider.php`:

```php
protected function gate(): void
{
    Gate::define('viewTelescope', function ($user) {
        return in_array($user->email, [
            // Añadir emails de usuarios autorizados
        ]);
    });
}
```

Para dar acceso a usuarios específicos en producción, añade sus emails al array.

## Watchers Disponibles

Telescope incluye watchers para:

- **Batch**: Monitorea batch jobs
- **Cache**: Operaciones de caché
- **Command**: Comandos Artisan
- **Dump**: Volcados de variables (dd, dump)
- **Event**: Eventos disparados
- **Exception**: Excepciones y errores
- **Gate**: Autorizaciones
- **HTTP Client**: Peticiones HTTP salientes
- **Job**: Jobs en cola
- **Log**: Logs de la aplicación
- **Mail**: Emails enviados
- **Model**: Operaciones Eloquent
- **Notification**: Notificaciones
- **Query**: Queries SQL
- **Redis**: Comandos Redis
- **Request**: Peticiones HTTP
- **Schedule**: Tareas programadas
- **View**: Renderizado de vistas

## Base de Datos

Telescope utiliza una tabla principal:
- `telescope_entries`: Almacena todas las entradas

## Comandos Útiles

### Limpiar entradas antiguas
```bash
vendor/bin/sail artisan telescope:prune
```

### Pausar grabación
```bash
vendor/bin/sail artisan telescope:pause
```

### Reanudar grabación
```bash
vendor/bin/sail artisan telescope:resume
```

### Limpiar caché
```bash
vendor/bin/sail artisan telescope:clear
```

## Modo Oscuro

Para habilitar el modo oscuro de Telescope, descomenta la siguiente línea en `TelescopeServiceProvider.php`:

```php
public function register(): void
{
    Telescope::night();
    // ...
}
```

## Consideraciones de Rendimiento

- En producción, considera usar `telescope:prune` regularmente para limpiar datos antiguos
- Configura los watchers específicos que necesites en `config/telescope.php`
- Usa el filtro en el service provider para limitar qué se registra

## Documentación Oficial

https://laravel.com/docs/10.x/telescope
