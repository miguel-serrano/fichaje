# Application Information

## Project Overview

**Control de Fichaje IA** (AI Time Tracking Control) is a Laravel-based application designed for managing time tracking with AI capabilities.

## Technical Stack

### Core Framework
- **PHP**: 8.2.29
- **Laravel**: v11 (^11.0)
- **Laravel Sail**: v1 - Docker development environment
- **Laravel Sanctum**: v4 - API authentication
- **Laravel Telescope**: v5 - Debugging and monitoring

### Key Dependencies
- **Tactician Command Bus**: `jagarsoft/laravel-tactician` v1.0 - CQRS implementation
- **OpenAI Laravel**: v0.9.1 - AI integration
- **Guzzle**: v7.2 - HTTP client
- **Ramsey UUID**: v4.0 - UUID generation

### Development Tools
- **Laravel Pint**: v1 - Code formatting
- **PHPUnit**: v11 - Testing framework
- **Laravel Boost**: v1.8 - MCP development server
- **Faker**: v1.9.1 - Test data generation

## Architecture

This application follows **Domain-Driven Design (DDD)** principles with a clear separation of concerns.

### DDD Structure

```
app/DDD/
├── Authentication/
│   ├── Application/    # Use cases, commands, handlers
│   ├── Domain/         # Entities, value objects, interfaces
│   └── Infrastructure/ # Implementations, persistence
├── TimeTracking/
│   ├── Application/
│   ├── Domain/
│   └── Services/
├── User/
│   ├── Application/
│   ├── Domain/
│   └── Infrastructure/
└── Shared/
    ├── Domain/         # Shared domain logic
    └── Infrastructure/ # Shared infrastructure
```

### Architectural Patterns

#### CQRS (Command Query Responsibility Segregation)
The application uses the Tactician command bus to separate commands from queries:
- **Commands**: Write operations that change state
- **Queries**: Read operations that return data
- **Handlers**: Process commands and queries

#### Repository Pattern
Domain repositories abstract data persistence:
- Interfaces defined in `Domain/Interface/`
- Implementations in `Infrastructure/Persistence/`

#### Service Providers
Domain-specific service providers bind implementations:
- `DDDServiceProvider` - Core DDD bindings
- `UserServiceProvider` - User domain bindings
- Additional providers per domain

## Development Environment

### Laravel Sail
This project runs inside Docker containers managed by Laravel Sail.

**All commands must be prefixed with `vendor/bin/sail`:**

```bash
# Start services
vendor/bin/sail up -d

# Stop services
vendor/bin/sail stop

# Artisan commands
vendor/bin/sail artisan migrate
vendor/bin/sail artisan test

# Composer
vendor/bin/sail composer install

# NPM
vendor/bin/sail npm run dev
vendor/bin/sail npm run build

# PHP scripts
vendor/bin/sail php [script]
```

### Database
- **Connection**: MySQL
- **Migrations**: Located in `database/migrations/`
- **Seeders**: Located in `database/seeders/`
- **Factories**: Located in `database/factories/`

### Mail
- **Development**: Mailpit (SMTP testing)
- **Port**: 1025

## Testing Strategy

### Test Types
- **Feature Tests**: Test full request/response cycles
- **Unit Tests**: Test individual components

### Test Organization
```
tests/
├── Feature/          # Integration tests
└── Unit/            # Unit tests
    └── User/
        └── Application/  # Domain-specific unit tests
```

### Running Tests
```bash
# All tests
vendor/bin/sail artisan test

# Specific file
vendor/bin/sail artisan test tests/Feature/ExampleTest.php

# Filter by name
vendor/bin/sail artisan test --filter=testName
```

## Code Quality

### Laravel Pint
Code must conform to the project's style rules:

```bash
# Fix formatting issues
vendor/bin/sail bin pint --dirty
```

### Conventions
- Constructor property promotion (PHP 8+)
- Explicit return type declarations
- Type hints for method parameters
- PHPDoc blocks for complex types
- Form Request classes for validation
- Eloquent relationships with return types

## Domains

### Authentication
Handles user authentication and authorization.

### User
Manages user entities and operations:
- User creation, updates, deletion
- User activation/deactivation
- User queries and retrieval

### TimeTracking
Manages time tracking functionality with AI capabilities.

### Shared
Contains cross-domain utilities and shared domain logic.

## Current State

### Active Branch: `refactor-user`
Recent refactoring work on the User domain:
- Implementing command bus pattern
- Removing direct command/handler implementations
- Improved authorization and error handling
- Repository pattern refinements

### Modified Files
- Repository interfaces and implementations
- Command handlers (Delete, GetById, ToggleActive)
- Service providers
- Tests updated to reflect new architecture

## Additional Resources

- Main configuration: `config/`
- Routes: `routes/`
- HTTP layer: `app/Http/`
- Eloquent models: Check domain Infrastructure layers
- Frontend assets: Managed by Vite

## AI Integration

The application includes OpenAI integration for AI-powered features, particularly related to time tracking analysis and automation.
