# Payment Gateway Extensibility

The payment system was designed using the Strategy Pattern.

Each payment gateway implements the same contract, allowing the application to process payments independently of a specific provider.

The current implementation integrates **Paymob** as the payment gateway. The controller depends only on the payment interface, while the concrete gateway is resolved by the application.

This design allows additional gateways (such as Stripe or PayPal) to be added with minimal changes to the existing code.

## Current Structure

```
app/
└── Providers/
    └── PaymentServiceProvider.phpjk
└── Services/
    └── Payment/
        ├── Contracts/
        │   └── PaymentGateway.php
        └── Gateways/
            └── BasePaymobGateway.php // abstract containing shared methods between sub classes
            └── PaymobGateway.php
```

## Paymob Configuration

The Paymob credentials are configured through the `.env` file.

Example:

```env
PAYMOB_CARD_INTEGRATION_ID=
PAYMOB_SECRET_KEY=
PAYMOB_PUBLIC_KEY=
PAYMOB_HMAC_SECRET=
PAYMOB_BASE_URL=
PAYMOB_API_KEY=
```

Keeping these values in the environment file allows credentials to be managed securely without modifying the application code.

## Adding a New Gateway

To add another payment gateway:

1. Create a class that implements the `PaymentGateway` interface.
2. Extend the BasePaymentGateway abstract class.
3. Implement the payment gateway processing logic.
4. Add the required configuration values to `.env`.

No changes are required in the controllers or order business logic.
Payment Gateway changes based on a route segment variable.

# Installation

## Prerequisites

Make sure you have the following installed:

- Docker
- Docker Compose

## Setup

Clone the repository:

```bash
git clone https://github.com/abdelrhman-sd/Order-and-Payment-Management-API.git
cd OrderAndPaymentManagementAPI
```

Copy the environment file:

```bash
cp .env.example .env
```

Update the required environment variables, including your database configuration and Paymob credentials.

Build and start the containers:

```bash
docker compose up --build -d
```

Generate the application key:

```bash
make key
```

Generate the JWT secret:

```bash
make jwt
```

Run the database migrations:

```bash
make migarate
```

Seed the database:

```bash
make seed
```

The API will now be available at:

```
http://localhost:8000
```

## Running Tests

Run the test suite with:

```bash
docker compose exec app php artisan test
```
## How the Payment Gateway Works

The payment module follows the Strategy Pattern through the `PaymentGateway` interface. Every payment gateway must implement the same set of operations, including payment initiation, payment verification, refunds, webhook processing, and payload normalization.

The application never communicates directly with a specific gateway implementation. Instead, it depends on the `PaymentGateway` contract, while the concrete gateway is resolved by the `PaymentServiceProvider` according to the gateway specified in the request.

Common payment functionality that is shared across gateways is implemented in the `BasePaymentGateway` abstract class. This includes processing gateway webhooks, updating payment and order statuses, handling refunds, and managing database transactions. Individual gateway implementations only need to provide provider-specific logic such as API communication and payload mapping.

The current implementation uses `PaymobGateway`, which is responsible for:

- Creating payment intentions through the Paymob API.
- Generating the checkout URL for the customer.
- Verifying payment status.
- Processing full and partial refunds.
- Converting Paymob responses into a standardized format used by the application.

This design allows new payment providers to be added by implementing the `PaymentGateway` interface, extending `BasePaymentGateway` when shared functionality is needed, registering the gateway in `PaymentServiceProvider`, and adding the required configuration values to the environment file. No changes are required in the controllers or the application's business logic.
