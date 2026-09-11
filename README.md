# National Dairy Authority — Client Satisfaction Survey (CSS) System

[![PHP Version](https://img.shields.io/badge/php-%5E8.1-blue.svg)](https://www.php.net/)
[![Laravel Framework](https://img.shields.io/badge/laravel-10.x-red.svg)](https://laravel.com/)

A centralized, automated client satisfaction measurement and feedback collection system developed for the **National Dairy Authority (NDA)**. The system enables clients to evaluate agency services through structured feedback, Citizen's Charter questions, and Service Quality Dimensions (SQD).

The application supports both **web portal survey submissions** and **API integration** with external client systems, complete with API key authentication and real-time webhook dispatching.

---

## Table of Contents

- [Features](#features)
- [System Architecture & Workflow](#system-architecture--workflow)
- [System Requirements](#system-requirements)
- [Installation & Local Setup](#installation--local-setup)
- [API Keys & Client Service Management](#api-keys--client-service-management)
  - [Security Architecture](#security-architecture)
  - [Generating API Keys via Artisan Command](#generating-api-keys-via-artisan-command)
  - [Authenticating API Requests](#authenticating-api-requests)
  - [Managing and Revoking Keys](#managing-and-revoking-keys)
- [API Reference](#api-reference)
  - [1. Create Survey Session (Pre-filled URL)](#1-create-survey-session-pre-filled-url)
  - [2. Submit Survey Response Directly via API](#2-submit-survey-response-directly-via-api)
  - [HTTP Status Codes & Error Handling](#http-status-codes--error-handling)
- [Webhooks & Real-Time Notifications](#webhooks--real-time-notifications)
  - [Event & Headers](#event--headers)
  - [Payload Schema](#payload-schema)
  - [Retry Policy & Delivery Logging](#retry-policy--delivery-logging)
- [Survey Validation & Business Rules](#survey-validation--business-rules)
  - [Citizen's Charter (CC) Logic](#citizens-charter-cc-logic)
  - [Conditional Remarks](#conditional-remarks)
  - [Field Locking & Tamper Prevention](#field-locking--tamper-prevention)
- [Reference Codes](#reference-codes)
  - [Center Codes](#center-codes)
  - [Region Codes](#region-codes)
  - [Service Code Samples](#service-code-samples)
- [Directory Structure](#directory-structure)

---

## Features

- **Public & Kiosk Web Form:** Clean, responsive client satisfaction survey supporting desktop, tablet, and mobile devices.
- **Pre-filled Survey Sessions:** External systems can issue pre-filled, time-limited survey URLs with locked fields to streamline respondent experience and ensure data integrity.
- **Direct API Submissions:** External applications can submit survey responses programmatically via authenticated REST endpoints.
- **API Key Authentication:** SHA-256 hashed API key management with prefix tracking and activity logging.
- **Real-Time Webhooks:** Asynchronous HTTP webhook notifications dispatched to client systems upon survey completion, with exponential backoff retries and delivery tracking.
- **Citizen's Charter & Service Quality Metrics:** Full standard CC questions (CC1 Awareness, CC2 Visibility, CC3 Helpfulness) and 9 Service Quality Dimensions (SQD0 to SQD8).
- **Mandatory Feedback on Low Ratings:** Automatically enforces detailed remarks if overall satisfaction is 3 (Neutral) or below.

---

## System Architecture & Workflow

```
+-----------------------------------------------------------------------------------+
|                            External Client Systems                               |
|                         (Integrated Client Services)                              |
+------------------------------------+----------------------------------------------+
                                     |
              1. Authenticated API Request (X-API-Key / Bearer)
                                     v
+-----------------------------------------------------------------------------------+
|                        CSS Client Authentication Middleware                       |
|                   (Verifies SHA-256 hash & active client status)                  |
+------------------------------------+----------------------------------------------+
                                     |
         +---------------------------+---------------------------+
         |                                                       |
         v (POST /api/survey-sessions)                           v (POST /api/survey-responses)
+----------------------------------+          +--------------------------------------+
|  Generate Survey Session Token   |          |    Direct API Survey Response        |
|  - Pre-fills verified attributes |          |    - Validates CC / SQD metrics      |
|  - Generates unique survey URL   |          |    - Saves response record           |
+-----------------+----------------+          +------------------+-------------------+
                  |                                              |
                  v                                              |
+----------------------------------+                             |
| Client Visits Pre-filled Web URL |                             |
|  - Locked fields read-only       |                             |
|  - Submits survey via browser    |                             |
+-----------------+----------------+                             |
                  |                                              |
                  +----------------------+-----------------------+
                                         |
                                         v
                         +-------------------------------+
                         | Record Completed Response     |
                         | Marks Session as Completed    |
                         +---------------+---------------+
                                         |
                                         v
                         +-------------------------------+
                         | Dispatch Async Webhook Job    |
                         | (SendSurveyCompletedWebhook)  |
                         +---------------+---------------+
                                         |
                                         v
                         +-------------------------------+
                         | POST payload to Client URL    |
                         | Log to webhook_deliveries     |
                         +-------------------------------+
```

---

## System Requirements

- **PHP:** `^8.1` or higher
- **Extensions:** `OpenSSL`, `PDO`, `Mbstring`, `Tokenizer`, `XML`, `Ctype`, `JSON`, `BCMath`, `cURL`
- **Database:** MySQL 8.0+, MariaDB 10.3+, SQLite 3.35+, or SQL Server
- **Package Managers:** Composer 2.x and Node.js 18+ (with NPM)

---

## Installation & Local Setup

### 1. Clone the Repository
```bash
git clone <repository-url>
cd client-satisfaction-survey
```

### 2. Install Dependencies
```bash
# Install PHP dependencies
composer install

# Install frontend JavaScript and CSS dependencies
npm install
```

### 3. Configure Environment
Copy the example environment file and customize your database credentials:
```bash
cp .env.example .env
```

Ensure your database connection details are configured in `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=client_satisfaction_survey
DB_USERNAME=root
DB_PASSWORD=your_password

# Set queue connection for asynchronous webhook processing
QUEUE_CONNECTION=database
```

### 4. Generate Application Encryption Key
```bash
php artisan key:generate
```

### 5. Run Database Migrations & Seeders
This migrates the database schema and seeds default centers, regions, standard services, and API clients:
```bash
php artisan migrate --seed
```

### 6. Build Frontend Assets
```bash
# For local asset compilation and hot reloading:
npm run dev

# Or compile optimized production assets:
npm run build
```

### 7. Run Local Development Server & Queue Worker
In terminal 1, start the Laravel web server:
```bash
php artisan serve
```

In terminal 2, start the queue worker to process background jobs (including webhook dispatching):
```bash
php artisan queue:work
```

---

## API Keys & Client Service Management

### Security Architecture

External services interacting with the survey API must provide a valid API key.
- **Key Format:** Default generated keys start with a prefix (e.g. `css_live_`) followed by 48 secure random alphanumeric characters.
- **One-Way Hashing:** Plain-text keys are **never stored** in the database. Only their SHA-256 cryptographic hash (`api_key_hash`) is saved.
- **Prefix Identification:** The first 12 characters are stored in `key_prefix` (e.g., `css_live_...`) for administrative reference.
- **Plain-text Display:** The full plain API key is printed **only once** at the time of creation.

---

### Generating API Keys via Artisan Command

The project includes an Artisan CLI command `client:create` (`App\Console\Commands\CreateApiClientCommand`) to register client services and generate keys.

#### 1. Generate an Auto-Generated Key
To register a new service with a randomly generated 48-character live key:
```bash
php artisan client:create "Client Service Name"
```

**Output:**
```
Client service [Client Service Name] registered successfully!

+----+---------------------+---------------------+--------------+--------+
| ID | Name                | Slug                | Key Prefix   | Status |
+----+---------------------+---------------------+--------------+--------+
| 1  | Client Service Name | client-service-name | css_live_... | Active |
+----+---------------------+---------------------+--------------+--------+

SAVE THIS API KEY NOW. It will not be shown again in plain text:
<YOUR_GENERATED_PLAIN_API_KEY>
```

#### 2. Specify a Custom Slug
```bash
php artisan client:create "Client Service Name" --slug=client-service
```

#### 3. Assign a Specific Plain API Key
If you need to assign a predetermined key:
```bash
php artisan client:create "Client Service Name" --slug=client-service --key="<your_custom_api_key>"
```

---

### Authenticating API Requests

Clients authenticate by including their plain API key in either of the following HTTP headers:

#### Option A: `X-API-Key` Header (Recommended)
```http
X-API-Key: <your_api_key>
```

#### Option B: `Authorization` Bearer Token Header
```http
Authorization: Bearer <your_api_key>
```

---

### Managing and Revoking Keys

- **Revoke / Deactivate Access:** Update the `is_active` column in the `api_clients` table to `false` (`0`). Requests with that key will immediately return `403 Forbidden`.
- **Track Usage:** The system automatically updates the `last_used_at` timestamp in `api_clients` on every successful authenticated request.

---

## API Reference

All API routes are prefixed with `/api` and protected by the `auth.client` middleware.

### 1. Create Survey Session (Pre-filled URL)

Generates a secure, single-use token and returns a pre-filled survey link that can be provided to respondents.

- **URL:** `POST /api/survey-sessions`
- **Headers:**
  - `Content-Type: application/json`
  - `X-API-Key: <your_api_key>` or `Authorization: Bearer <your_api_key>`

#### Request Body Parameters:

| Parameter | Type | Required | Description |
| :--- | :--- | :--- | :--- |
| `client_system` | string | Optional | Identifier of calling service (defaults to authenticated client's slug) |
| `external_transaction_id` | string | Optional | External reference / transaction ID (e.g. `TX-100234`) |
| `respondent_name` | string | Optional | Pre-filled respondent full name |
| `respondent_contact_number` | string | Optional | Pre-filled respondent phone number |
| `division_office` | string | Optional | Office / division delivering service |
| `client_type` | string | Optional | `Citizen`, `Business`, or `Government(Employee or Another Agency)` |
| `date_service_availed` | string (YYYY-MM-DD) | Optional | Date service was rendered (cannot be in the future) |
| `sex` | string | Optional | `Male`, `Female`, `Intersex`, or `Prefer not to say` |
| `age` | integer | Optional | Respondent age (1–120) |
| `center_id` or `center_code` | int / string | Optional | Center ID or standard code (e.g., `CO`, `NL`, `SL`) |
| `region_id` or `region_code` | int / string | Optional | Region ID or standard code (e.g., `NCR`, `R03`) |
| `service_id` or `service_code` | int / string | Optional | Service ID or standard code (e.g., `SRV-AB-NATURAL-HEAT`) |
| `expires_in_hours` | integer | Optional | Session lifetime in hours (Default: `168` [7 days], Min: 1, Max: 720) |
| `webhook_url` | string (URL) | Optional | HTTPS callback endpoint to receive notification when survey is completed |

#### Example Request:
```bash
curl -X POST http://localhost:8000/api/survey-sessions \
  -H "Content-Type: application/json" \
  -H "X-API-Key: YOUR_API_KEY_HERE" \
  -d '{
    "external_transaction_id": "TX-100234",
    "respondent_name": "Juan Dela Cruz",
    "respondent_contact_number": "09171234567",
    "center_code": "CO",
    "region_code": "NCR",
    "service_code": "SRV-AB-NATURAL-HEAT",
    "division_office": "Operations Division",
    "client_type": "Citizen",
    "date_service_availed": "2026-09-11",
    "sex": "Male",
    "age": 34,
    "expires_in_hours": 72,
    "webhook_url": "https://external-system.example.com/api/css-webhook"
  }'
```

#### Example Response (`201 Created`):
```json
{
  "success": true,
  "message": "Survey session created successfully.",
  "data": {
    "token": "76483fb3e84ca6dd983f2a8c081e7d012480ad213271bc8f8c92a911e548f072",
    "survey_url": "http://localhost:8000/survey?token=76483fb3e84ca6dd983f2a8c081e7d012480ad213271bc8f8c92a911e548f072",
    "expires_at": "2026-09-14T12:00:00+08:00"
  }
}
```

---

### 2. Submit Survey Response Directly via API

Allows external client systems to submit survey ratings directly.

- **URL:** `POST /api/survey-responses`
- **Headers:**
  - `Content-Type: application/json`
  - `X-API-Key: <your_api_key>` or `Authorization: Bearer <your_api_key>`

#### Request Body Parameters:

| Parameter | Type | Required | Description |
| :--- | :--- | :--- | :--- |
| `session_token` | string | Optional | Token from pre-created session (enforces locked values) |
| `external_transaction_id`| string | Optional | External reference ID |
| `webhook_url` | string | Optional | Webhook callback URL |
| `center_id` / `center_code`| int / string | Required | Operating center |
| `division_office` | string | Required | Division or office |
| `client_type` | string | Required | `Citizen`, `Business`, `Government(Employee or Another Agency)` |
| `date_service_availed` | string (YYYY-MM-DD)| Required | Past or current date |
| `sex` | string | Required | `Male`, `Female`, `Intersex`, `Prefer not to say` |
| `age` | integer | Required | Respondent age (1–120) |
| `region_id` / `region_code`| int / string | Required | Region |
| `service_id` / `service_code`| int / string | Required | Service availed |
| `overall_satisfaction` | integer | Required | Rating scale `1` (Terrible) to `10` (Delighted) |
| `remarks` | string | Conditional | **Required** if `overall_satisfaction` is 1, 2, or 3 |
| `cc1_awareness` | integer | Required | `1`: Know CC & saw it, `2`: Know CC but not seen, `3`: Learned CC from office, `4`: Do not know CC |
| `cc2_visibility` | integer | Conditional | `1` to `5` (Easy to see -> Not visible). **Required if CC1 is 1–3; Prohibited if CC1 is 4** |
| `cc3_helpfulness` | integer | Conditional | `1` to `4` (Helped very much -> Did not help). **Required if CC1 is 1–3; Prohibited if CC1 is 4** |
| `sqd0_overall` to `sqd8_outcome` | integer | Required | Values `0` to `5` (0=N/A, 1=Strongly Disagree, 2=Disagree, 3=Neither, 4=Agree, 5=Strongly Agree) |

#### Example Request:
```bash
curl -X POST http://localhost:8000/api/survey-responses \
  -H "Content-Type: application/json" \
  -H "X-API-Key: YOUR_API_KEY_HERE" \
  -d '{
    "respondent_name": "Maria Santos",
    "respondent_contact_number": "09181234567",
    "center_code": "CO",
    "region_code": "NCR",
    "service_code": "SRV-AB-NATURAL-HEAT",
    "division_office": "Operations Division",
    "client_type": "Citizen",
    "date_service_availed": "2026-09-11",
    "sex": "Female",
    "age": 29,
    "overall_satisfaction": 5,
    "remarks": null,
    "cc1_awareness": 1,
    "cc2_visibility": 1,
    "cc3_helpfulness": 1,
    "sqd0_overall": 5,
    "sqd1_responsiveness": 5,
    "sqd2_reliability": 5,
    "sqd3_access_facilities": 5,
    "sqd4_communication": 5,
    "sqd5_costs": 5,
    "sqd6_integrity": 5,
    "sqd7_assurance": 5,
    "sqd8_outcome": 5,
    "webhook_url": "https://external-system.example.com/api/css-webhook"
  }'
```

#### Example Response (`201 Created`):
```json
{
  "success": true,
  "message": "Survey response submitted successfully.",
  "data": {
    "response_id": 15,
    "survey_id": 15,
    "session_token": "a8f192...",
    "client_system": "client-service",
    "external_transaction_id": null,
    "webhook_status": "queued"
  }
}
```

---

### HTTP Status Codes & Error Handling

The API returns consistent JSON responses for error states:

| Status Code | Reason | Example Response |
| :--- | :--- | :--- |
| `401 Unauthorized` | Missing or invalid API key | `{"success": false, "message": "Unauthenticated client service. Invalid API key."}` |
| `403 Forbidden` | Client key is disabled / inactive | `{"success": false, "message": "Client service is inactive or unauthorized."}` |
| `404 Not Found` | Session token does not exist | `{"success": false, "message": "The provided survey session token does not exist."}` |
| `409 Conflict` | Session has already been submitted | `{"success": false, "message": "This survey session has already been completed."}` |
| `422 Unprocessable Entity` | Validation failure or expired session | `{"message": "Remarks is required when the overall rating is 3 and below.", "errors": {...}}` |

---

## Webhooks & Real-Time Notifications

When a survey session or direct response is submitted with a configured `webhook_url`, the system dispatches an asynchronous background job (`SendSurveyCompletedWebhook`).

### Event & Headers

The webhook POST request contains the following HTTP headers:
- `Content-Type: application/json`
- `User-Agent: ClientSatisfactionSurvey-Webhook/1.0`
- `X-Webhook-Event: survey.completed`
- `X-Webhook-Delivery: <delivery_id>`
- `X-Webhook-Timestamp: <ISO-8601 Timestamp>`

### Payload Schema

```json
{
  "event": "survey.completed",
  "timestamp": "2026-09-11T12:30:00+08:00",
  "data": {
    "session_token": "76483fb3e84ca6dd983f2a8c081e7d012480ad213271bc8f8c92a911e548f072",
    "client_system": "client-service",
    "external_transaction_id": "TX-100234",
    "response_id": 15,
    "survey_id": 15,
    "respondent": {
      "name": "Juan Dela Cruz",
      "contact_number": "09171234567",
      "client_type": "Citizen",
      "sex": "Male",
      "age": 34
    },
    "service": {
      "id": 1,
      "code": "SRV-AB-NATURAL-HEAT",
      "name": "Animal Breeding Services - Dairy Herd (Natural Heat)"
    },
    "center": {
      "id": 1,
      "code": "CO",
      "name": "National (Central Office)"
    },
    "region": {
      "id": 15,
      "code": "NCR",
      "name": "NCR - National Capital Region"
    },
    "division_office": "Operations Division",
    "date_service_availed": "2026-09-11",
    "ratings": {
      "overall_satisfaction": 5,
      "remarks": null,
      "citizen_charter": {
        "cc1_awareness": 1,
        "cc2_visibility": 1,
        "cc3_helpfulness": 1
      },
      "sqd": {
        "sqd0_overall": 5,
        "sqd1_responsiveness": 5,
        "sqd2_reliability": 5,
        "sqd3_access_facilities": 5,
        "sqd4_communication": 5,
        "sqd5_costs": 5,
        "sqd6_integrity": 5,
        "sqd7_assurance": 5,
        "sqd8_outcome": 5
      }
    },
    "completed_at": "2026-09-11T12:30:00+08:00"
  }
}
```

### Retry Policy & Delivery Logging

- **Attempts:** Up to 3 attempts.
- **Backoff Schedule:** 10 seconds $\to$ 60 seconds $\to$ 300 seconds (5 minutes).
- **Delivery Log Table:** Every attempt, response code, response body, and error message is persisted in the `webhook_deliveries` table for auditing and debugging.

---

## Survey Validation & Business Rules

### Citizen's Charter (CC) Logic
- **CC1 (Awareness):**
  - Options 1, 2, or 3: The respondent knows of CC $\to$ **CC2** (Visibility) and **CC3** (Helpfulness) are **required**.
  - Option 4 ("I do not know what a CC is and I did not see one"): **CC2 and CC3 are prohibited and automatically saved as `null`**.

### Conditional Remarks
- If `overall_satisfaction` is $\le 3$ (Neutral, Dissatisfied, Terrible), the `remarks` field is **strictly required** to capture actionable feedback.
- If `overall_satisfaction` is $> 3$, remarks are optional.

### Field Locking & Tamper Prevention
When a respondent opens a pre-filled survey link (`/survey?token=...`), any field populated during session creation (e.g. Center, Region, Service, Respondent Name, Date Availed) is:
1. Rendered as disabled / read-only in the UI.
2. Verified and strictly enforced on the server upon submission to prevent client-side HTML tampering.

---

## Reference Codes

### Center Codes (`form_options.category = 'center'`)

| Code | Center Name |
| :--- | :--- |
| `CO` | National (Central Office) |
| `NL` | North Luzon |
| `SL` | South Luzon |
| `CV` | Central Visayas |
| `WV` | Western Visayas |
| `NM` | North Mindanao |
| `SM` | South Mindanao |

### Region Codes (`form_options.category = 'region'`)

| Code | Region Name | Code | Region Name |
| :--- | :--- | :--- | :--- |
| `NCR` | National Capital Region | `R07` | Region VII - Central Visayas |
| `CAR` | Cordillera Administrative Region | `R08` | Region VIII - Eastern Visayas |
| `R01` | Region I - Ilocos Region | `R09` | Region IX - Zamboanga Peninsula |
| `R02` | Region II - Cagayan Valley | `R10` | Region X - Northern Mindanao |
| `R03` | Region III - Central Luzon | `R11` | Region XI - Davao Region |
| `R04A`| Region IV-A - CALABARZON | `R12` | Region XII - SOCCSKSARGEN |
| `R04B`| Region IV-B - MIMAROPA | `R13` | Region XIII - Caraga |
| `R05` | Region V - Bicol Region | `BARMM`| Bangsamoro Autonomous Region |
| `R06` | Region VI - Western Visayas | | |

### Service Code Samples (`services.code`)

- `SRV-AB-NATURAL-HEAT`: Animal Breeding Services - Dairy Herd (Natural Heat)
- `SRV-AB-SYNC-AI`: Animal Breeding Services - Synchronized / Fixed-Time AI
- `SRV-LOAN-IMPORT-FINAL`: Loan of Dairy Animals (Imported) - Conduct of Final Evaluation (If Imported Animals)
- `SRV-MILK-FEEDING-PROG`: Milk Feeding Program Activities
- `SRV-MILK-TEST-PHYS`: Milk Testing Services (Physico-Chemical Analyses)
- `SRV-DBO-REGISTRATION`: Registration and Licensing of DBOs (Registration of DBOs)
*(For a complete list of 47 services, consult `database/seeders/ServiceSeeder.php`)*

---

## Directory Structure

```
client-satisfaction-survey/
├── app/
│   ├── Console/
│   │   └── Commands/
│   │       └── CreateApiClientCommand.php   # 'php artisan client:create'
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/
│   │   │   │   ├── SurveyResponseController.php # Direct API submission endpoint
│   │   │   │   └── SurveySessionController.php  # Survey session token endpoint
│   │   │   └── SurveyController.php             # Web portal survey routes
│   │   ├── Middleware/
│   │   │   └── AuthenticateClientService.php    # API key validation middleware
│   │   └── Requests/
│   │       ├── Api/
│   │       │   └── StoreSurveyResponseRequest.php
│   │       ├── CreateSurveySessionRequest.php
│   │       └── StoreSurveyRequest.php
│   ├── Jobs/
│   │   └── SendSurveyCompletedWebhook.php       # Async queue job for webhooks
│   └── Models/
│       ├── ApiClient.php                        # API client model & key hashing
│       ├── FormOption.php                       # Centers & Regions lookup
│       ├── Service.php                          # Services catalog
│       ├── SurveyResponse.php                   # Survey submissions & ratings
│       ├── SurveySession.php                    # Token sessions & pre-fill status
│       └── WebhookDelivery.php                  # Webhook audit log & status
├── database/
│   ├── migrations/                              # Database schemas
│   └── seeders/                                 # Seeders for options, services & clients
├── resources/
│   └── views/
│       ├── survey/
│       │   ├── confirmation.blade.php           # Post-submission confirmation page
│       │   ├── create.blade.php                 # Main survey web form
│       │   └── invalid.blade.php                # Expired / completed session notice
│       └── welcome.blade.php                    # Landing page
├── routes/
│   ├── api.php                                  # Authenticated API endpoints
│   └── web.php                                  # Public web survey routes
└── tests/
    └── Feature/                                 # Automated feature tests
```
