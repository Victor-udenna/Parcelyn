# Parcelyn Presentation Master Script (10 Speakers)

This file is a ready-to-share presentation script for all 10 team members.
Each person has:
- Scope of what to present
- Slide-by-slide speaking notes
- Live demo cues
- Likely questions and prepared answers

Use this in order from Speaker 1 to Speaker 10.

---

## Presentation Plan (Recommended Timing)

- Total presentation time: 30 to 40 minutes
- Speaker time target: 3 to 4 minutes each
- Live demo: spread across Speakers 3, 4, 6, 7, 8

Flow:
1. Introduction and project context
2. Architecture and stack
3. Authentication and session flow
4. Dashboard and operations overview
5. Parcel creation and pricing logic
6. Pricing zone management
7. Public tracking and status lifecycle
8. Invoice and printable invoice
9. Database schema, setup, and deployment flow
10. Security review, limitations, roadmap, and close

---

## Speaker 1 - Hamzat Basirat (Project Introduction and Problem Statement)

### Scope
- Introduce the project
- Explain the problem being solved
- Explain what the system does end-to-end
- Introduce team and repository context

### Script
Good day everyone. Our project is called Parcelyn, a parcel delivery management system built with PHP and MySQL.

The core problem we solved is this: delivery records are often managed manually, making parcel tracking, cost calculation, and status updates inconsistent. Parcelyn centralizes this process into one web application.

At a high level, the system supports:
- Admin login
- Parcel creation
- Automatic tracking number generation
- Pricing by delivery zone and parcel weight
- Delivery status updates
- Invoice generation
- Public parcel tracking without login

Our implementation is built as a traditional multi-page PHP application, where each feature is implemented in a dedicated PHP file, with MySQL handling persistent storage.

The key benefit is operational clarity. A sender creates a parcel once, and from that point the system handles traceability through tracking number, status pipeline, and invoice records.

This project was developed by a 10-member team, with each member handling specific implementation and documentation responsibilities that we are presenting in sections today.

### Slide Cues
- Slide 1: Project title + team names
- Slide 2: Problem statement
- Slide 3: Core capabilities list

### Likely Q&A
- Q: Why this project?
  - A: It solves a practical logistics process and demonstrates CRUD, authentication, status workflow, and invoice generation in one system.
- Q: Who are the users?
  - A: Primary user is authenticated admin/staff; public users can only track by tracking number.

---

## Speaker 2 - Andrew Shaibu (System Architecture and Technology Stack)

### Scope
- Explain architecture style
- Explain technology choices
- Explain file structure and request flow

### Script
Parcelyn follows a simple but effective architecture: server-rendered PHP pages with shared database access through PDO.

Technology stack:
- Backend: PHP
- Database: MySQL or MariaDB
- Database access layer: PDO with prepared statements
- Frontend: HTML, CSS, and vanilla JavaScript
- Authentication: PHP sessions
- Password security: password hashing and verification

The application is file-based, not framework-based. This makes learning and deployment straightforward for small projects.

Core architectural pattern:
1. Browser requests a PHP page.
2. Page checks session if authentication is required.
3. Page runs PDO queries.
4. Data is rendered as HTML.
5. JavaScript provides lightweight interactivity where needed, like copy tracking number or live cost preview.

Main files map directly to features:
- index.php for login
- dashboard.php for operations summary
- send_parcel.php for creating parcels
- pricing.php for zone pricing management
- update_status.php for changing delivery status
- invoice.php and print_invoice.php for billing view
- track_parcel.php for public parcel lookup
- db.php for centralized database connection

This structure is ideal for academic delivery systems because each file is easy to read, trace, and maintain.

### Slide Cues
- Slide 4: Architecture diagram (browser -> PHP page -> MySQL)
- Slide 5: Tech stack table
- Slide 6: File-to-feature mapping

### Likely Q&A
- Q: Why not Laravel?
  - A: We intentionally used plain PHP for transparency and educational value; each logic layer is visible.
- Q: How is SQL injection reduced?
  - A: Prepared statements are used in core query paths.

---

## Speaker 3 - Arikpo, Uveri Peter (Authentication and Session Management)

### Scope
- Explain login flow in detail
- Explain session variables and route guarding
- Demo login and logout

### Script
Authentication starts at index.php.

When the login form is submitted, the server:
1. Reads email and password from POST.
2. Fetches the user record by email using a prepared statement.
3. Verifies password with password_verify.
4. If valid, sets session values and redirects to dashboard.

Session variables stored on login are:
- user_id
- user_name
- user_role

These session values are then used to protect internal pages. For example, dashboard, send parcel, pricing, update status, and invoice all check if user_id exists in session before showing content.

Logout flow is handled in logout.php:
- session_start
- session_destroy
- redirect to login

This gives us basic but functional access control for internal operations.

### Demo Cues
- Open login page
- Log in with demo@example.com / password123
- Show redirect to dashboard
- Click logout and show redirect back to login

### Likely Q&A
- Q: Is registration included?
  - A: No. User registration is not included; users are seeded from setup SQL or created manually in DB.
- Q: Is role actually enforced?
  - A: Role is stored in session, but admin-only enforcement is a recommended next improvement.

---

## Speaker 4 - Odetola Emmanuel Precious (Dashboard and Operational Visibility)

### Scope
- Explain dashboard KPIs and table
- Explain interaction behavior
- Demo dashboard actions

### Script
After login, the dashboard gives a snapshot of delivery operations.

It runs aggregate queries to show:
- Total parcels
- Pending parcels
- In Transit parcels
- Delivered parcels

It also fetches the latest 20 parcels in descending creation order and renders them in a table with:
- Tracking number
- Sender
- Receiver
- Receiver address
- Weight
- Current status badge
- Date
- Action links

Interactive behavior:
- Tracking number has a copy button for quick sharing.
- Table rows are clickable and route to invoice view.
- Update and Invoice quick links are available per parcel.

Operationally, this page is important because it combines overview metrics and immediate actions in one place.

### Demo Cues
- Show stat cards changing after creating a parcel
- Click copy icon on tracking number
- Use Update and Invoice links from table actions

### Likely Q&A
- Q: Why only 20 rows?
  - A: Current implementation intentionally limits to recent 20 for simplicity; pagination is listed in roadmap.
- Q: How are status colors handled?
  - A: A server-side helper maps each status to a badge color.

---

## Speaker 5 - Orji Joseph (Send Parcel Workflow and Cost Formula)

### Scope
- Explain parcel creation form
- Explain tracking ID generation
- Explain cost computation and DB insertion
- Demo sending a parcel

### Script
The send parcel module is the core transaction entry point.

Form fields include:
- Receiver full name
- Receiver phone
- Receiver address
- Weight
- Zone selection
- Optional description

When submitted, server-side logic does the following:
1. Generates tracking number using SWP- prefix plus 8 characters.
2. Loads selected pricing zone from database.
3. Calculates cost using formula:
   Total Cost = Base Price + (Weight x Price per kg)
4. Inserts parcel record with sender details, receiver details, zone, and computed cost.
5. Returns success message with generated tracking number and invoice link.

There is also live JavaScript cost preview before submission, so users can see base price, weight charge, and total cost instantly as they select zone and weight.

This reduces estimation ambiguity and improves user confidence before saving the parcel.

### Demo Cues
- Open send parcel page
- Enter weight and change zone to show live total update
- Submit form
- Show success message with tracking number and invoice link

### Likely Q&A
- Q: Is cost recalculated server-side or only in JS?
  - A: Both. JS is only preview; server recalculates for actual persistence.
- Q: Can invalid zone break calculation?
  - A: If zone is missing it can error, so stronger validation is recommended in hardening.

---

## Speaker 6 - Timothy Kenneth (Pricing Zone Management)

### Scope
- Explain pricing page CRUD
- Explain pricing business model
- Demo add/edit/delete zone

### Script
Pricing management is in pricing.php and provides CRUD operations for delivery zones.

Each zone stores:
- Zone name
- Base price
- Price per kg
- Description

Feature coverage:
- Add new zone via POST form
- Edit zone through modal form
- Delete zone by id
- List all zones sorted by price per kg

Business formula shown on the page:
Total Cost = Base Price + (Weight x Price per kg)

This makes pricing fully configurable by operations staff without code changes.

Important behavior:
If a zone is already referenced by parcels, deletion may fail due to foreign key constraints, which preserves referential integrity.

### Demo Cues
- Add a temporary new zone
- Edit the same zone from modal
- Attempt delete and explain foreign key effect if linked

### Likely Q&A
- Q: Is delete action safe as GET?
  - A: It works, but best practice is POST with CSRF token; this is listed under security improvements.
- Q: Are amounts validated against negatives?
  - A: Basic HTML numeric input exists; stronger server-side validation is recommended.

---

## Speaker 7 - Favour Ifurukpe (Public Tracking and Status Lifecycle)

### Scope
- Explain no-login tracking feature
- Explain status timeline logic
- Explain status update process
- Demo both pages

### Script
Public parcel tracking is handled in track_parcel.php.

A user enters tracking number, and the system performs an uppercase normalized search in parcels table.
If a match exists, it displays:
- Tracking number
- Sender
- Receiver
- Receiver address
- Parcel weight
- Delivery progress timeline

Timeline stages are:
- Pending
- Picked Up
- In Transit
- Out for Delivery
- Delivered

Status updates are handled in update_status.php for authenticated users.
User selects one status from dropdown, and the selected status is updated in parcels table.

Allowed update statuses include an extra state, Cancelled.
Note that Cancelled is valid in database and update page, but not part of the public timeline steps, so timeline defaults to early-stage index behavior when status is outside tracked step list.

### Demo Cues
- Copy a tracking number from dashboard
- Open tracking page without login and search
- Open update status page, change status, then refresh tracking page

### Likely Q&A
- Q: Why expose receiver address publicly?
  - A: Current implementation does; for privacy, this should be reduced in production.
- Q: Is status history stored?
  - A: Not yet. Current model stores only current status; status history table is planned.

---

## Speaker 8 - Victor Okwuosa (Invoice Generation and Printable Invoice)

### Scope
- Explain invoice data composition
- Explain payment status update
- Explain print-friendly invoice
- Demo invoice and print view

### Script
Invoice generation is in invoice.php and combines parcel record with pricing zone data using a join.

Invoice content includes:
- Tracking number
- Date
- Payment status
- Sender and receiver info
- Description and zone
- Weight and rate per kg
- Base price
- Weight charge
- Total cost
- Delivery status

Payment handling:
- If status is Unpaid, a Mark as Paid action is available.
- Action updates payment_status to Paid.

Printable version is provided by print_invoice.php, designed for print dialog and PDF export.

Key difference:
- invoice.php is login-protected
- print_invoice.php currently does not enforce session login

That means direct URL with valid id can expose invoice data; session protection should be added as a security hardening step.

### Demo Cues
- Open invoice from dashboard
- Show line-item and total calculation
- Click Mark as Paid
- Open print view and show Save as PDF action

### Likely Q&A
- Q: Is payment gateway integrated?
  - A: Not yet. Payment status is manual in current version.
- Q: Is invoice cost from DB or recomputed?
  - A: Total cost is stored in parcel record; invoice also shows computed weight component from zone rate and weight.

---

## Speaker 9 - Abigail Ehonwa (Database Design, Setup, and Environment)

### Scope
- Explain schema and relationships
- Explain setup flow and demo data
- Explain setup.sql vs setup.php difference

### Script
Parcelyn uses database parcel_db with three core tables:
- users
- price_zones
- parcels

Relationships:
- users.id to parcels.sender_id (one user can create many parcels)
- price_zones.id to parcels.zone_id (one zone can apply to many parcels)

Schema highlights:
- parcels has status enum and payment_status enum
- cost is persisted as decimal
- tracking number is unique
- foreign keys preserve data consistency

Setup path recommended for this project is setup.sql because it creates all required structures including price_zones, zone_id, cost, and payment_status fields.

A seeded admin account is available:
- demo@example.com
- password123

Important implementation note:
There is also setup.php in the project, but it is inconsistent with current schema and includes hardcoded hosted credentials. It creates a simpler parcels table without current pricing-related columns and should not be used as the primary setup for this codebase.

### Demo Cues
- Briefly open setup.sql and point out key tables
- Show demo login works after setup.sql import

### Likely Q&A
- Q: Why keep both setup files?
  - A: setup.php appears to be legacy or alternate environment script; setup.sql is canonical for this project version.
- Q: Can this run on XAMPP quickly?
  - A: Yes. Import setup.sql, update db.php credentials if needed, then run php built-in server.

---

## Speaker 10 - Akore Mercy (Security Review, Limitations, Future Roadmap, Conclusion)

### Scope
- Present risks found
- Present practical improvements
- Present known limitations
- Close presentation

### Script
Our project is functional and suitable for learning and controlled deployment, but we identified clear hardening areas before production use.

Security concerns and fixes:
1. Hardcoded credentials in setup.php should be removed, and exposed passwords rotated.
2. print_invoice.php should require login session.
3. CSRF protection should be added to mutating forms.
4. Destructive actions currently using GET should be converted to POST.
5. Input validation should be stricter for numeric and status fields.
6. Role-based access should be enforced using stored session role.
7. Detailed DB errors should be hidden in production and logged securely.

Known limitations:
- No registration page
- No pagination and filters on dashboard
- No parcel deletion workflow
- No notification integration
- No audit trail for status changes

Future roadmap:
- Add parcel_status_history table
- Add role management pages
- Add dashboard search and filters
- Add payment gateway integration
- Add email/SMS notifications
- Add environment-based configuration and secrets management
- Consider migration to structured MVC or framework for scale

Conclusion:
Parcelyn demonstrates complete lifecycle management of parcel operations from creation to tracking and invoicing. It combines practical logistics workflow with core software engineering concepts: authentication, relational modeling, state transitions, business rule computation, and operational reporting.

Thank you.

### Slide Cues
- Security checklist slide
- Limitations and roadmap slide
- Final summary slide

### Likely Q&A
- Q: What is first production priority?
  - A: Authentication and authorization hardening plus CSRF and invoice access protection.
- Q: What is first product priority?
  - A: Status history and dashboard filtering for real operational visibility.

---

## Team Distribution Message (Copy and Send to Group)

Use this exact message in your group chat:

Team, our presentation parts are fixed as follows:
1. Hamzat Basirat - Project intro, problem statement, full system overview.
2. Andrew Shaibu - Architecture, stack, and file structure.
3. Arikpo Uveri Peter - Login, sessions, route protection, logout demo.
4. Odetola Emmanuel Precious - Dashboard metrics, table behavior, operations view.
5. Orji Joseph - Send parcel flow, tracking generation, cost logic, parcel creation demo.
6. Timothy Kenneth - Pricing zone CRUD and business pricing configuration.
7. Favour Ifurukpe - Public tracking, timeline logic, status update workflow.
8. Victor Okwuosa - Invoice module, mark-as-paid flow, print invoice demo.
9. Abigail Ehonwa - Database schema, relationships, setup workflow, seed data.
10. Akore Mercy - Security review, limitations, future improvements, final conclusion.

Please rehearse your section with demo cues and Q&A notes from this script.

---

## Coordinator Notes (For You)

- Ask each speaker to keep within 3 to 4 minutes.
- If total time is short, reduce demos to Speakers 3, 5, and 8 only.
- Keep one person controlling the laptop while each speaker talks.
- During Q&A, route technical DB questions to Speaker 9 and security questions to Speaker 10.

---

## Optional Extra: 60-Second Backup Summary (If Time Is Cut)

Parcelyn is a PHP-MySQL parcel management system that supports authenticated parcel creation, dynamic zone-based pricing, tracking number generation, parcel status updates, invoice generation, printable invoices, and public tracking by tracking number. It uses PDO prepared statements, session-based authentication, and a relational schema linking users, zones, and parcels. The main hardening priorities are CSRF protection, role enforcement, securing printable invoice access, replacing GET-based mutations with POST, and moving secrets to environment configuration.