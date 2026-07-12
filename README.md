# TransitOps — Smart Transport Operations Platform

A full-stack fleet operations platform built for the 8-hour hackathon brief:
vehicle, driver, dispatch, maintenance, and expense management with enforced
business rules and role-based operational dashboards.

## Stack
- **Backend:** Python Flask + Flask-SQLAlchemy (SQLite)
- **Frontend:** Server-rendered HTML/CSS (Jinja2 templates), Chart.js for analytics
- **Auth:** Lightweight role-selection (no external auth provider needed for demo)

## Quick Start

```bash
cd transitops
pip install -r requirements.txt
python app.py
```

Then open **http://localhost:5000** in your browser.

The database (`transitops.db`) is created automatically on first run and
seeded with demo vehicles, drivers, trips, and expenses so the dashboards are
populated immediately — no manual setup needed for your demo.

To reset the demo data, just delete `transitops.db` and restart the app.

## Roles

Pick a role on the login screen — each one sees a tailored dashboard and has
different permissions, matching the hackathon brief's target users:

| Role | Can do |
|---|---|
| **Fleet Manager** | Full CRUD on vehicles, schedule/complete maintenance, view all dashboards |
| **Driver** | Create and manage trips/dispatch, log expenses |
| **Safety Officer** | Manage drivers, update safety scores, monitor license compliance |
| **Financial Analyst** | View cost/expense analytics, log expenses (read-focused) |

## Business Rules Enforced

- A vehicle **cannot be dispatched** while under maintenance or marked inactive.
- A driver **cannot be assigned** to a trip if their license is expired or they're off duty.
- **No double-booking** — the system blocks any new trip that overlaps an existing scheduled/ongoing trip for the same vehicle or driver.
- Scheduling maintenance **automatically takes a vehicle out of service**; it can't be dispatched again until maintenance is marked complete.
- Completing a trip **updates the vehicle's odometer** and frees the vehicle/driver for the next dispatch.
- Vehicles/drivers involved in active trips **cannot be deleted**.

## Modules

- **Dashboard** — KPIs, fleet status breakdown, expense trend, maintenance & license alerts, role-specific panels
- **Vehicles** — registration, status tracking, maintenance due dates
- **Drivers** — roster, license expiry tracking, safety scores
- **Dispatch / Trips** — create trips with live validation, start/complete/cancel lifecycle
- **Maintenance** — schedule and complete service records, auto vehicle status sync
- **Expenses** — fuel/toll/maintenance/other logging, category breakdown, cost analytics

## Project Structure

```
transitops/
├── app.py                  # Flask app: models, routes, business logic, seed data
├── requirements.txt
├── templates/               # Jinja2 templates
│   ├── base.html            # Shared shell/sidebar/nav
│   ├── login.html
│   ├── dashboard.html
│   ├── vehicles.html / vehicle_form.html
│   ├── drivers.html / driver_form.html
│   ├── trips.html / trip_form.html
│   ├── maintenance.html / maintenance_form.html
│   └── expenses.html / expense_form.html
└── static/
    ├── css/style.css        # Full design system
    └── js/main.js
```
