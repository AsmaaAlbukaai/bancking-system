# Advanced Banking System — README

**Author:** ME  
**Last updated:** 2025-12-21  
**Language:** English

A modular, extensible banking system built with Laravel. The project demonstrates effective application of both behavioral and structural design patterns to meet real-world banking requirements while keeping the architecture flexible, maintainable, and testable.

---

## Table of Contents

- Overview  
- Non-Functional Requirements (NFRs)  
- Design Patterns Applied (with code references)  
- Architecture Overview  
- Installation & Local Setup  
- Docker Usage  
- API Documentation (Swagger/OpenAPI)  
- Testing & Code Coverage (PHPUnit + PCOV)  
- Load Testing (JMeter)  
- Project Workflow & Task Management (Jira)  
- Contributing & Code Style  
- Folder Map  

---

## Overview

This repository implements core banking operations (accounts, transactions, interest calculation, payments, notifications, reports, customer support) following SOLID principles and well-chosen design patterns. The system is divided into cohesive Laravel modules that encapsulate domain behavior and isolate infrastructure concerns.

### Key highlights

- Clean separation of concerns via Services / Controllers / Repositories  
- Runtime-extensible strategies for account behavior and interest calculation  
- Robust transaction workflow with multi-level approvals  
- Adapter-based external payment integration  
- High test coverage with unit and feature tests  
- Ready-to-run Docker environment and JMeter load plans  

---

## Non-Functional Requirements (NFRs)

Each NFR below includes a short “How we satisfy it” mapping to code and mechanisms.

### Extensibility

**Goal:** Add new account types, interest methods, gateways, or approval steps with minimal changes.

**How:**  
- Strategy pattern for account operations and interest calculation  
- Factories for strategy/state selection  
- Adapter for payment gateways  
- Chain-of-responsibility for approvals  

**References:**
- Account strategies: `app/Modules/Account/Strategies/*`
- Interest strategies: `app/Modules/Interest/Strategies/*`
- Payment adapters: `app/Modules/Payment/Gateways/*`
- Approval chain: `app/Modules/Transaction/Handlers/*`

---

### Maintainability

**Goal:** Easy to understand, change, and debug.

**How:**  
- Clear module boundaries  
- Facade hides complexity  
- Repositories abstract persistence  
- Consistent naming and testing  

**References:**
- Facade: `app/Modules/Banking/BankFacade.php`
- Repository:  
  - Interface: `app/Modules/Account/Contracts/AccountRepositoryInterface.php`  
  - Implementation: `app/Repositories/AccountRepository.php`

---

### Performance

**Goal:** Efficient operations at scale.

**How:**  
- Composite pattern for aggregated balances  
- Background jobs for recurring tasks  
- Caching in interest calculations  

**References:**
- Composite: `app/Modules/Account/AccountCompositeService.php`
- Jobs: `app/Jobs/ProcessRecurringTransactionsJob.php`
- Caching: `app/Modules/Interest/InterestCalculatorService.php`

---

### Security

**Goal:** Protect financial operations and APIs.

**How:**  
- Authentication via Laravel Sanctum  
- Role-based authorization  
- Input validation  
- Rule-based approvals  

**References:**
- Middleware: `app/Http/Middleware/AdminMiddleware.php`
- Controllers validation: `AccountController`, `TransactionController`

---

### Testability

**Goal:** Independent, repeatable tests with high coverage.

**How:**  
- Contracts + dependency injection  
- Mocks for external services  
- SQLite in-memory testing  
- PCOV for coverage  

**References:**
- Tests: `tests/Unit/*`, `tests/Feature/*`
- PHPUnit config: `phpunit.xml`

---

## Design Patterns Applied (with code references)

### Behavioral Patterns

**Strategy (Account behavior)**  
- Interface: `AccountStrategy.php`  
- Implementations: `SavingsStrategy.php`, `CheckingStrategy.php`  
- Factory: `AccountStrategyFactory.php`

**Strategy (Interest calculation)**  
- Interface: `InterestStrategyInterface.php`  
- Implementations: `SimpleInterestStrategy.php`, `CompoundInterestStrategy.php`  
- Factory: `InterestStrategyFactory.php`

**Chain of Responsibility (Transaction approvals)**  
- Base: `BaseApprovalHandler.php`  
- Handlers: `AutoApprovalHandler.php`, `TellerApprovalHandler.php`, `ManagerApprovalHandler.php`

**Observer-like (Notifications)**  
- Dispatcher: `NotificationDispatcher.php`  
- Notifiers: `EmailNotifier.php`, `SMSNotifier.php`, `InAppNotifier.php`

---

### Structural Patterns

**State (Account lifecycle)**  
- States: `ActiveState.php`, `FrozenState.php`, `ClosedState.php`  
- Factory: `AccountStateFactory.php`

**Composite (Account trees)**  
- `AccountCompositeService.php`

**Adapter (Payment gateways)**  
- Interface: `PaymentGatewayAdapterInterface.php`  
- Implementations: `StripeGatewayAdapter.php`, `DummyGatewayAdapter.php`

**Facade**  
- `app/Modules/Banking/BankFacade.php`

**Repository**  
- Interface + Implementation separation

---

## Architecture Overview

- Controllers: `app/Modules/*/*Controller.php`
- Services encapsulate business logic
- Facade orchestrates complex flows
- Strategies and factories enable runtime behavior changes
- Handlers manage approval workflows
- Repositories isolate persistence

---

## Installation & Local Setup

**Requirements:** PHP 8.2+, Composer, Node.js, MySQL/SQLite, Docker (optional)

```bash
git clone <repo-url>
cd banking-system
composer install
cp .env.example .env
docker-compose up -d --build
php artisan key:generate
php artisan migrate --seed
php artisan l5-swagger:generate

---

## Enjoy building on a clean, extensible Laravel architecture