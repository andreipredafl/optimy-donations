# Challenge Solution Overview

## Quick Start with Docker

### Prerequisites

- [Docker](https://docs.docker.com/get-docker/) installed on your machine
- [Docker Compose](https://docs.docker.com/compose/install/) (included with Docker Desktop)

### Running the Application

```bash
# Clone the repository
git clone git@github.com:andreipredafl/optimy-donations.git
cd optimy-donations

# Start the application
docker-compose up --build
```

The application will be available at: **http://localhost:8000**

### Database Seeding

Run seeders manually to populate test data:

```bash
docker-compose exec app php artisan db:seed
```

### Queue Worker (for Email Notifications)

To process email notifications after donations, start the queue worker:

```bash
docker-compose exec app php artisan queue:work
```

### What's Included in Docker Setup

- PHP 8.4 with Laravel 12
- Node.js 20 for frontend assets
- SQLite database (persisted in `./database/database.sqlite`)
- Nginx web server
- All dependencies pre-installed

---

## 1. Introduction & Tech Stack

This project is a solution for the challenge, built with:

- **Laravel 12** (backend)
- **Vue.js 3** (frontend, with Inertia.js starter kit)
- **SQLite** (database)
- **phpstan** (level 8 static analysis; extensive PHPDoc comments, especially in models, are already created to support this)
- **spatie/laravel-permission** (roles & permissions, with custom seeder)

## 2. Main Functionalities

- Authentication system (starter kit)
- Dashboard page
- Campaigns page: cards, filter by category, search by keyword
- Campaign detail page: progress bar, donate button & popup, recent donations, anonymous donation option
- Donations page: employee sees their donations and received donations for their campaigns; admin sees all donations
- User management page (admin only)
- Email notifications on donation (via queued job & notification; requires mail env and running queue)

## 3. Architecture Overview

- **Backend:** Laravel, modular structure (controllers, services, models, contract interfaces, middleware, notification and job classes, providers)
- **Frontend:** Vue 3 + Inertia.js, Vite for fast builds
- **Components:** Organized by feature (Donation, Payment, UI)
- **Config:** Environment-based, sensitive data not hardcoded
- **Dependency Management:** Composer (PHP), NPM (JS)

## 4. Roles & Permissions

- Managed with `spatie/laravel-permission` and a custom seeder
- **Backend:** Permissions are passed to Inertia in `HandleInertiaRequests`:

```php
'can' => $request->user()
    ? $request->user()->getPermissionsViaRoles()
        ->pluck('name')
        ->mapWithKeys(function ($permissionName) use ($request) {
            return [$permissionName => $request->user()->can($permissionName)];
        })
        ->toArray()
    : [],
```

- **Frontend:** Permissions are accessed in Vue.js:

```js
const page = usePage();
const can = computed(() => page.props.can ?? {});

const mainNavItems: NavItem[] = [
    {
        title: 'Campaigns',
        href: '/campaigns',
        icon: Target,
        show: can.value['campaigns.view'],
    },
    // ...
]
```

## 5. Payment System

- **Goal:** Easily switch payment providers (mock, Stripe, etc.) with minimal code changes
- **Config:** Providers and settings in `config/payment.php`, default set via env
- **Change Payment Processor:**
    - Set the payment driver in your `.env` file:
        ```env
        PAYMENT_DRIVER=mock   # or PAYMENT_DRIVER=stripe
        ```
    - Then clear and cache config:
        ```sh
        php artisan config:cache
        ```
- **Core Classes:**
    - `PaymentManager`: Loads and returns the correct payment service (mock, Stripe, etc.) based on config. Ensures all drivers implement `PaymentServiceInterface`.
    - `PaymentServiceInterface`: All payment providers must implement:
        - `processPayment()`
        - `verifyPayment()`
        - `refundPayment()`
        - `getName()`
        - `isAvailable()`
    - `MockPaymentService`: Simulates payment success/failure for development/testing. Returns mock transaction IDs and error messages for different scenarios.
    - `StripePaymentService`: Placeholder for real Stripe integration. Can be enabled in config and swapped in at any time.
- **Interaction:**
    - When a donation is made, the app uses `PaymentManager` to select the active payment driver. The driver handles the payment logic (real or mock). This makes it easy to switch providers or add new ones without changing business logic.

## 6. UI Components

- **Reusable UI:** Located in `resources/js/components/ui/` (buttons, dialogs, inputs, cards, checkboxes, etc.)
- **Main App Components:**
    - `AppHeader.vue`, `AppSidebar.vue`, `AppContent.vue`, `AppShell.vue`: Layout/navigation
    - `AppEmptyState.vue`, `AppPagination.vue`, `AppDatePicker.vue`: Utility/feedback
- **Donation Components:**
    - `Donation/Modal.vue`: Donation form and payment flow
    - `Donation/Card.vue`, `Donation/Recent.vue`, `Donation/Creator.vue`: Display donation info
- **Payment Components:**
    - `Payment/Mock/CreditCardForm.vue`: Simulates credit card form for mock/test payments
    - `Payment/Stripe/CreditCardForm.vue`: Placeholder for Stripe integration

## 7. Note on Card Validation

- Card details validation is currently done in the `StoreDonationRequest` form request. This is only for demonstration purposes with the mock payment system.
- In a real production environment, card validation should be handled securely by the payment provider, not in the backend or form request.

## 8. Testing Instructions

- **Test successful donation:** Use card `4532 1234 5678 9010`
- **Test payment decline:** Use card `4532 1234 5678 9011`
- **Test insufficient funds:** Use card `4532 1234 5678 9012`

After these tests, the system will be fully functional and ready for production use!

## 9. Email Notifications & Queue

- After a successful donation, an email notification is sent via a Laravel job (dispatched and registered in the `jobs` table).
- To process notifications:
    - Start the queue worker:
        ```sh
        php artisan queue:work
        ```
    - Configure email details in your `.env`:
        ```env
        MAIL_MAILER=log
        MAIL_SCHEME=null
        MAIL_HOST=127.0.0.1
        MAIL_PORT=2525
        MAIL_USERNAME=null
        MAIL_PASSWORD=null
        MAIL_FROM_ADDRESS="hello@example.com"
        MAIL_FROM_NAME="${APP_NAME}"
        ```

## 10. Permissions Cache

- If you change roles or permissions, reset the cache:
    ```sh
    php artisan permission:cache-reset
    ```

## Test Accounts

You can use the following test accounts to log in and explore the system:

- **Admin:**  
  Email: `admin@acme.com`  
  Password: `password`

- **Employee/User:**  
  Email: `jane.doe@acme.com`  
  Password: `password`

## Test Suite

The application includes comprehensive tests covering core functionality:

- **Test Structure:** Organized by entity in `tests/Feature/` with folders for Campaign, Category, Payment, Auth, and Settings
- **Coverage:** 52 tests with 161 assertions covering CRUD operations, payment processing, validation, authorization, and error handling
- **Mock Payment Testing:** Includes scenarios for successful payments, declined cards, and insufficient funds using the test card numbers mentioned above
- **Run Tests:**
    ```sh
    php artisan test
    ```
- **Run Specific Module:**
    ```sh
    php artisan test tests/Feature/Campaign/
    php artisan test tests/Feature/Payment/
    ```

Key test areas include campaign management, donation processing with mock payments, category relationships, and user authentication flows.
