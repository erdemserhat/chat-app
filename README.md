# Chat Application API

A modern, scalable chat application built with **Slim Framework 4** following **Clean Architecture** principles. This project demonstrates enterprise-level software design patterns, SOLID principles, and comprehensive testing strategies.

> **Note**: This project was developed as a technical assessment for **Bunq**, showcasing modern PHP development practices and architectural excellence.

## 📁 Project Structure

```
chat-app/
├── src/
│   ├── Domain/                    # Business Logic Layer
│   │   ├── User/                 # User entity and exceptions
│   │   ├── ChatGroup/            # ChatGroup entity and exceptions
│   │   └── Message/              # Message entity and exceptions
│   │
│   ├── Application/              # Application Layer
│   │   ├── Actions/              # HTTP Controllers
│   │   │   ├── UserAction.php
│   │   │   ├── ChatGroupAction.php
│   │   │   └── MessageAction.php
│   │   ├── Validators/           # Input validation
│   │   │   ├── UserValidator.php
│   │   │   └── ChatGroupValidator.php
│   │   └── Services/             # Application services
│   │       └── ResponseService.php
│   │
│   └── Infrastructure/           # Infrastructure Layer
│       ├── Repository/           # Data access
│       │   ├── UserRepository.php
│       │   ├── ChatGroupRepository.php
│       │   └── MessageRepository.php
│       └── Logger/               # Logging implementation
│
├── tests/                        # Comprehensive test suite
│   ├── Domain/                   # Unit tests for entities
│   ├── Application/              # Action/service tests
│   ├── Integration/              # Cross-layer tests
│   ├── Scenarios/                # Real-world scenarios
│   ├── Performance/              # Performance benchmarks
│   ├── Security/                 # Security validation
│   └── EdgeCases/                # Edge case handling
│
├── config/                       # Configuration
│   ├── dependencies.php         # DI container setup
│   ├── routes.php               # API route definitions
│   └── settings.php             # Application settings
│
├── database/                     # Database files
└── public/                       # Web server entry point
```

## 🏗️ Architecture Overview

This application implements **Clean Architecture** (Uncle Bob's architecture), ensuring separation of concerns, dependency inversion, and maintainability. The architecture consists of three main layers:

### **Domain Layer** (Business Logic)
- **Pure business entities** with no external dependencies
- **Domain exceptions** for business rule violations
- **Value objects** representing core business concepts
- Located in: `src/Domain/`

### **Application Layer** (Use Cases)
- **Actions** - HTTP request handlers (Controllers)
- **Validators** - Input validation and sanitization
- **Services** - Application-specific business logic
- **DTOs** - Data transfer objects
- Located in: `src/Application/`

### **Infrastructure Layer** (External Concerns)
- **Repositories** - Data persistence abstraction
- **Database** - SQLite with Doctrine DBAL
- **Logging** - Monolog integration
- **Configuration** - Environment-based settings
- Located in: `src/Infrastructure/`

### **Architecture Benefits**
- ✅ **Testability** - Easy unit testing with dependency injection
- ✅ **Maintainability** - Clear separation of concerns
- ✅ **Scalability** - Framework-agnostic business logic
- ✅ **Flexibility** - Easy to swap implementations
- ✅ **SOLID Principles** - Single responsibility, dependency inversion

## 🛠️ Technology Stack

- **Framework**: Slim 4 (Microframework)
- **Architecture**: Clean Architecture + SOLID Principles
- **Database**: SQLite with Doctrine DBAL
- **Dependency Injection**: PHP-DI Container
- **Logging**: Monolog
- **Testing**: PHPUnit (76 tests, 2242 assertions)
- **Code Quality**: PHPStan (Level 8), PHP_CodeSniffer
- **Web Server**: Apache .htaccess with security & performance optimization
- **PHP Version**: 7.4+ | 8.0+

## 🚀 Getting Started

### Prerequisites
```bash
# Required
PHP 7.4+ with extensions: json, pdo, sqlite3
Composer 2.0+

# Optional (for development)
Git
PHPStan
PHP_CodeSniffer
```

### Installation & Setup

1. **Clone and Install Dependencies**
   ```bash
   git clone https://github.com/erdemserhat/chat-app.git
   cd chat-app
   composer install
   ```

2. **Environment Configuration**
   ```bash
   cp .env.example .env
   # Configure database path and other settings in .env
   ```

3. **Database Setup**
   ```bash
   # Setup database tables and initial data
   composer setup-db
   
   # Or run directly
   php scripts/setup_database.php
   ```

4. **Start Development Server**
   ```bash
   # Using composer script (multiple port options)
   composer start        # Port 8080
   composer start:8081   # Port 8081 (if 8080 is busy)
   composer start:8082   # Port 8082
   composer start:3000   # Port 3000
   
   # Or directly
   php -S localhost:8080 -t public
   
   # Kill existing server if port is busy
   composer kill-server
   ```

5. **Demo & Test API**
   ```bash
   # Run API demonstration with sample data
   composer demo
   
   # Or run directly  
   php scripts/demo_api.php
   ```

6. **Verify Installation**
   ```bash
   curl http://localhost:8080
   # Should return API welcome message with endpoints
   ```

### Development Commands

```bash
# Testing
composer test                    # Run all tests
composer test:unit              # Domain + Application tests
composer test:scenarios         # Real-world scenario tests
composer test:coverage          # Generate coverage report

# Code Quality
composer phpstan                # Static analysis
composer cs-check              # Code style check
composer cs-fix                # Auto-fix code style

# Database & Setup
composer setup-db              # Setup database tables
composer demo                  # Run API demo with sample data

# Development
composer start                  # Start dev server (port 8080)
composer start:8081             # Start dev server (port 8081)
composer start:8082             # Start dev server (port 8082)
composer start:3000             # Start dev server (port 3000)
composer kill-server            # Kill server on port 8080
```

## 📚 API Documentation

### Base URL
```
http://localhost:8080/api
```

### Response Format
All responses follow a consistent JSON structure:

**Success Response:**
```json
{
  "id": 1,
  "username": "john_doe",
  "created_at": "2024-01-01 10:00:00"
}
```

**Array Response:**
```json
[
  {
    "id": 1,
    "username": "john_doe",
    "created_at": "2024-01-01 10:00:00"
  }
]
```

**Error Response:**
```json
{
  "error": {
    "type": "VALIDATION_ERROR",
    "description": "Username is required"
  }
}
```

---

## 👥 User Management Endpoints

### Create User
**POST** `/api/users`

Creates a new user account with validation.

**Request:**
```json
{
  "username": "john_doe"
}
```

**Validation Rules:**
- Username: required, 3-50 chars, alphanumeric + dots/hyphens/underscores
- Must be unique across the system

**Response (201):**
```json
{
  "id": 1,
  "username": "john_doe"
}
```

### List Users
**GET** `/api/users`

Retrieves all registered users.

**Response (200):**
```json
[
  {
    "id": 1,
    "username": "john_doe",
    "created_at": "2024-01-01 10:00:00"
  }
]
```

### Get User Details
**GET** `/api/users/{id}`

Retrieves specific user information.

**Response (200):**
```json
{
  "id": 1,
  "username": "john_doe",
  "created_at": "2024-01-01 10:00:00"
}
```

---

## 💬 Group Management Endpoints

### Create Group
**POST** `/api/groups`

Creates a new chat group.

**Request:**
```json
{
  "name": "Development Team",
  "user_id": 1
}
```

**Business Rules:**
- Group names must be unique
- Creator automatically becomes a member
- Name: 2-100 characters

**Response (201):**
```json
{
  "id": 1,
  "name": "Development Team",
  "created_by": 1,
  "created_at": "2024-01-01 09:00:00"
}
```

### List Groups
**GET** `/api/groups`

Retrieves all available chat groups.

**Response (200):**
```json
[
  {
    "id": 1,
    "name": "Development Team",
    "created_by": 1,
    "created_at": "2024-01-01 09:00:00"
  }
]
```

### Join Group
**POST** `/api/groups/{id}/join`

Adds a user to an existing group.

**Request:**
```json
{
  "user_id": 2
}
```

**Business Rules:**
- User must exist
- Group must exist
- User cannot join twice
- Automatic membership validation

**Response (200):**
```json
{
  "message": "Successfully joined the group",
  "group_name": "Development Team",
  "user_id": 2
}
```

### Leave Group
**POST** `/api/groups/{id}/leave`

Removes a user from a group with business rule enforcement.

**Request:**
```json
{
  "user_id": 2
}
```

**Business Rules:**
- ⚠️ **Group creators cannot leave their own groups**
- User must be a current member
- Proper cleanup of user associations

**Response (200):**
```json
{
  "message": "Successfully left the group",
  "group_name": "Development Team",
  "user_id": 2
}
```

**Error Response (403):**
```json
{
  "error": {
    "type": "FORBIDDEN",
    "description": "Group creator cannot leave the group. Transfer ownership or delete the group instead."
  }
}
```

---

## 📨 Messaging Endpoints

### Send Message
**POST** `/api/messages/group/{group_id}`

Sends a message to a group with membership validation.

**Request:**
```json
{
  "user_id": 1,
  "content": "Hello team!"
}
```

**Business Rules:**
- User must be a group member
- Content cannot be empty
- Message persistence with timestamps

**Response (201):**
```json
{
  "id": 1,
  "group_id": 1,
  "user_id": 1,
  "content": "Hello team!",
  "created_at": "2024-01-01 10:00:00"
}
```

### Get Group Messages
**GET** `/api/messages/group/{group_id}`

Retrieves all messages from a specific group.

**Response:**
```json
[
  {
    "id": 1,
    "group_id": 1,
    "user_id": 1,
    "content": "Hello team!",
    "created_at": "2024-01-01 10:00:00"
  }
]
```

---

## 🔍 Complete Workflow Example

```bash
# 1. Create users
curl -X POST http://localhost:8080/api/users \
  -H "Content-Type: application/json" \
  -d '{"username": "alice"}'

curl -X POST http://localhost:8080/api/users \
  -H "Content-Type: application/json" \
  -d '{"username": "bob"}'

# 2. Create a group (alice as creator)
curl -X POST http://localhost:8080/api/groups \
  -H "Content-Type: application/json" \
  -d '{"name": "Project Alpha", "user_id": 1}'

# 3. Bob joins the group
curl -X POST http://localhost:8080/api/groups/1/join \
  -H "Content-Type: application/json" \
  -d '{"user_id": 2}'

# 4. Send messages
curl -X POST http://localhost:8080/api/messages/group/1 \
  -H "Content-Type: application/json" \
  -d '{"user_id": 1, "content": "Welcome to Project Alpha!"}'

# 5. Bob can leave (but alice cannot, as she is the creator)
curl -X POST http://localhost:8080/api/groups/1/leave \
  -H "Content-Type: application/json" \
  -d '{"user_id": 2}'
```

---

## 🧪 Testing Strategy

### Comprehensive Test Suite (76 Tests, 2242 Assertions)

- **Unit Tests** - Domain entities and application services
- **Integration Tests** - Cross-layer functionality
- **Scenario Tests** - Real-world usage patterns
- **Performance Tests** - Load and memory benchmarks
- **Security Tests** - XSS, SQL injection, input validation
- **Edge Case Tests** - Boundary conditions and error handling

```bash
# Run specific test suites
composer test:unit              # Fast unit tests
composer test:scenarios         # Business scenario validation
composer test:integration       # End-to-end functionality
```

## 🔒 Security Implementation

- **Input Validation** - Comprehensive validation layer
- **SQL Injection Prevention** - Parameterized queries
- **XSS Protection** - Output sanitization
- **Business Rule Enforcement** - Domain-level constraints
- **Error Handling** - Secure error responses without information leakage

## 🚨 HTTP Status Codes

| Code | Usage | Description |
|------|-------|-------------|
| `200` | Success | Standard successful response |
| `201` | Created | Resource created successfully |
| `400` | Bad Request | Validation errors, malformed requests |
| `403` | Forbidden | Business rule violations (e.g., creator leaving group) |
| `404` | Not Found | Resource does not exist |
| `409` | Conflict | Unique constraint violations |
| `500` | Server Error | Unexpected server errors |

---

## 📊 Performance Metrics

- **Response Time**: <50ms average for CRUD operations
- **Memory Usage**: <50MB for handling 6000+ objects
- **Concurrent Users**: Supports 50+ simultaneous connections
- **Database**: Optimized queries with proper indexing

## 🤝 Development Standards

- **PSR-4** autoloading
- **PSR-12** coding standards
- **PHPStan Level 8** static analysis
- **Clean Architecture** principles
- **SOLID** design principles
- **Comprehensive documentation**

---

**Developed by**: Serhat Erdem (serhaterdem961@gmail.com)  
**GitHub**: https://github.com/erdemserhat  
**Architecture**: Clean Architecture with SOLID Principles  
**Purpose**: Technical assessment for Bunq
