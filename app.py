import os
from datetime import datetime, timedelta, date
from flask import Flask, render_template, request, redirect, url_for, session, flash, jsonify
from flask_sqlalchemy import SQLAlchemy

BASE_DIR = os.path.abspath(os.path.dirname(__file__))

app = Flask(__name__)
app.config['SECRET_KEY'] = 'transitops-hackathon-secret'
app.config['SQLALCHEMY_DATABASE_URI'] = 'sqlite:///' + os.path.join(BASE_DIR, 'transitops.db')
app.config['SQLALCHEMY_TRACK_MODIFICATIONS'] = False

db = SQLAlchemy(app)

# ---------------------------------------------------------------------------
# MODELS
# ---------------------------------------------------------------------------

class Vehicle(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    plate_number = db.Column(db.String(20), unique=True, nullable=False)
    vehicle_type = db.Column(db.String(50), nullable=False)
    capacity_kg = db.Column(db.Float, default=0)
    odometer_km = db.Column(db.Float, default=0)
    status = db.Column(db.String(20), default='available')  # available, on_trip, maintenance, inactive
    last_maintenance_date = db.Column(db.Date, nullable=True)
    next_maintenance_due = db.Column(db.Date, nullable=True)
    created_at = db.Column(db.DateTime, default=datetime.utcnow)

    trips = db.relationship('Trip', backref='vehicle', lazy=True)
    maintenances = db.relationship('Maintenance', backref='vehicle', lazy=True)
    expenses = db.relationship('Expense', backref='vehicle', lazy=True)

    def maintenance_due_soon(self):
        if not self.next_maintenance_due:
            return False
        return self.next_maintenance_due <= date.today() + timedelta(days=7)

    def maintenance_overdue(self):
        if not self.next_maintenance_due:
            return False
        return self.next_maintenance_due < date.today()


class Driver(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    name = db.Column(db.String(100), nullable=False)
    phone = db.Column(db.String(20))
    license_number = db.Column(db.String(50), unique=True, nullable=False)
    license_expiry = db.Column(db.Date, nullable=False)
    status = db.Column(db.String(20), default='available')  # available, on_trip, off_duty
    safety_score = db.Column(db.Integer, default=100)
    created_at = db.Column(db.DateTime, default=datetime.utcnow)

    trips = db.relationship('Trip', backref='driver', lazy=True)

    def license_expired(self):
        return self.license_expiry < date.today()

    def license_expiring_soon(self):
        return date.today() <= self.license_expiry <= date.today() + timedelta(days=30)


class Trip(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    vehicle_id = db.Column(db.Integer, db.ForeignKey('vehicle.id'), nullable=False)
    driver_id = db.Column(db.Integer, db.ForeignKey('driver.id'), nullable=False)
    origin = db.Column(db.String(120), nullable=False)
    destination = db.Column(db.String(120), nullable=False)
    start_time = db.Column(db.DateTime, nullable=False)
    end_time = db.Column(db.DateTime, nullable=False)
    distance_km = db.Column(db.Float, default=0)
    status = db.Column(db.String(20), default='scheduled')  # scheduled, ongoing, completed, cancelled
    notes = db.Column(db.String(255))
    created_at = db.Column(db.DateTime, default=datetime.utcnow)

    expenses = db.relationship('Expense', backref='trip', lazy=True)


class Maintenance(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    vehicle_id = db.Column(db.Integer, db.ForeignKey('vehicle.id'), nullable=False)
    maintenance_type = db.Column(db.String(80), nullable=False)
    scheduled_date = db.Column(db.Date, nullable=False)
    completed_date = db.Column(db.Date, nullable=True)
    cost = db.Column(db.Float, default=0)
    description = db.Column(db.String(255))
    status = db.Column(db.String(20), default='scheduled')  # scheduled, in_progress, completed
    next_due_days = db.Column(db.Integer, default=90)
    created_at = db.Column(db.DateTime, default=datetime.utcnow)


class Expense(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    vehicle_id = db.Column(db.Integer, db.ForeignKey('vehicle.id'), nullable=False)
    trip_id = db.Column(db.Integer, db.ForeignKey('trip.id'), nullable=True)
    category = db.Column(db.String(30), nullable=False)  # fuel, toll, maintenance, other
    amount = db.Column(db.Float, nullable=False)
    liters = db.Column(db.Float, nullable=True)  # for fuel entries
    expense_date = db.Column(db.Date, nullable=False)
    notes = db.Column(db.String(255))
    created_at = db.Column(db.DateTime, default=datetime.utcnow)


ROLES = {
    'fleet_manager': 'Fleet Manager',
    'driver': 'Driver',
    'safety_officer': 'Safety Officer',
    'financial_analyst': 'Financial Analyst',
}

# ---------------------------------------------------------------------------
# HELPERS
# ---------------------------------------------------------------------------

def current_role():
    return session.get('role')


def require_role(*roles):
    r = current_role()
    if r is None:
        return False
    if roles and r not in roles:
        return False
    return True


@app.context_processor
def inject_globals():
    return dict(current_role=current_role(), role_label=ROLES.get(current_role(), ''), ROLES=ROLES)


def parse_dt(value):
    return datetime.strptime(value, '%Y-%m-%dT%H:%M')


def overlapping_trips_for_vehicle(vehicle_id, start, end, exclude_trip_id=None):
    q = Trip.query.filter(
        Trip.vehicle_id == vehicle_id,
        Trip.status.in_(['scheduled', 'ongoing']),
        Trip.start_time < end,
        Trip.end_time > start,
    )
    if exclude_trip_id:
        q = q.filter(Trip.id != exclude_trip_id)
    return q.all()


def overlapping_trips_for_driver(driver_id, start, end, exclude_trip_id=None):
    q = Trip.query.filter(
        Trip.driver_id == driver_id,
        Trip.status.in_(['scheduled', 'ongoing']),
        Trip.start_time < end,
        Trip.end_time > start,
    )
    if exclude_trip_id:
        q = q.filter(Trip.id != exclude_trip_id)
    return q.all()


# ---------------------------------------------------------------------------
# AUTH (simple role selection - hackathon scope)
# ---------------------------------------------------------------------------

@app.route('/', methods=['GET', 'POST'])
def login():
    if request.method == 'POST':
        role = request.form.get('role')
        name = request.form.get('name') or ROLES.get(role, 'User')
        if role in ROLES:
            session['role'] = role
            session['user_name'] = name
            return redirect(url_for('dashboard'))
        flash('Please select a valid role.', 'error')
    return render_template('login.html')


@app.route('/logout')
def logout():
    session.clear()
    return redirect(url_for('login'))


# ---------------------------------------------------------------------------
# DASHBOARD
# ---------------------------------------------------------------------------

@app.route('/dashboard')
def dashboard():
    if not require_role():
        return redirect(url_for('login'))

    vehicles = Vehicle.query.all()
    drivers = Driver.query.all()
    trips = Trip.query.all()

    vehicle_status_counts = {}
    for v in vehicles:
        vehicle_status_counts[v.status] = vehicle_status_counts.get(v.status, 0) + 1

    maintenance_alerts = [v for v in vehicles if v.maintenance_due_soon() or v.maintenance_overdue()]
    license_alerts = [d for d in drivers if d.license_expired() or d.license_expiring_soon()]

    active_trips = [t for t in trips if t.status in ('scheduled', 'ongoing')]

    total_expenses = db.session.query(db.func.sum(Expense.amount)).scalar() or 0
    fuel_expenses = db.session.query(db.func.sum(Expense.amount)).filter(Expense.category == 'fuel').scalar() or 0
    maintenance_cost = db.session.query(db.func.sum(Maintenance.cost)).scalar() or 0

    # monthly expense trend (last 6 months)
    monthly_trend = {}
    for e in Expense.query.all():
        key = e.expense_date.strftime('%Y-%m')
        monthly_trend[key] = monthly_trend.get(key, 0) + e.amount
    monthly_labels = sorted(monthly_trend.keys())[-6:]
    monthly_values = [round(monthly_trend[k], 2) for k in monthly_labels]

    # cost by vehicle
    cost_by_vehicle = {}
    for e in Expense.query.all():
        cost_by_vehicle[e.vehicle_id] = cost_by_vehicle.get(e.vehicle_id, 0) + e.amount
    vehicle_cost_labels = []
    vehicle_cost_values = []
    for vid, cost in sorted(cost_by_vehicle.items(), key=lambda x: -x[1])[:6]:
        v = Vehicle.query.get(vid)
        vehicle_cost_labels.append(v.plate_number if v else str(vid))
        vehicle_cost_values.append(round(cost, 2))

    completed_trips = [t for t in trips if t.status == 'completed']
    trip_counts_by_vehicle = {}
    for t in completed_trips:
        trip_counts_by_vehicle[t.vehicle_id] = trip_counts_by_vehicle.get(t.vehicle_id, 0) + 1
    utilization = []
    for v in vehicles:
        cnt = trip_counts_by_vehicle.get(v.id, 0)
        utilization.append({'plate': v.plate_number, 'trips': cnt})
    utilization.sort(key=lambda x: -x['trips'])

    return render_template(
        'dashboard.html',
        vehicles=vehicles,
        drivers=drivers,
        trips=trips,
        active_trips=active_trips,
        vehicle_status_counts=vehicle_status_counts,
        maintenance_alerts=maintenance_alerts,
        license_alerts=license_alerts,
        total_expenses=total_expenses,
        fuel_expenses=fuel_expenses,
        maintenance_cost=maintenance_cost,
        monthly_labels=monthly_labels,
        monthly_values=monthly_values,
        vehicle_cost_labels=vehicle_cost_labels,
        vehicle_cost_values=vehicle_cost_values,
        utilization=utilization[:8],
        today=date.today(),
    )


# ---------------------------------------------------------------------------
# VEHICLES
# ---------------------------------------------------------------------------

@app.route('/vehicles')
def vehicles_list():
    if not require_role():
        return redirect(url_for('login'))
    vehicles = Vehicle.query.order_by(Vehicle.id.desc()).all()
    return render_template('vehicles.html', vehicles=vehicles, today=date.today())


@app.route('/vehicles/new', methods=['GET', 'POST'])
def vehicle_new():
    if not require_role('fleet_manager'):
        flash('Only Fleet Managers can add vehicles.', 'error')
        return redirect(url_for('vehicles_list'))
    if request.method == 'POST':
        try:
            v = Vehicle(
                plate_number=request.form['plate_number'].strip().upper(),
                vehicle_type=request.form['vehicle_type'],
                capacity_kg=float(request.form.get('capacity_kg') or 0),
                odometer_km=float(request.form.get('odometer_km') or 0),
                status='available',
            )
            db.session.add(v)
            db.session.commit()
            flash(f'Vehicle {v.plate_number} added successfully.', 'success')
            return redirect(url_for('vehicles_list'))
        except Exception as e:
            db.session.rollback()
            flash(f'Error adding vehicle: {e}', 'error')
    return render_template('vehicle_form.html', vehicle=None)


@app.route('/vehicles/<int:vehicle_id>/edit', methods=['GET', 'POST'])
def vehicle_edit(vehicle_id):
    if not require_role('fleet_manager'):
        flash('Only Fleet Managers can edit vehicles.', 'error')
        return redirect(url_for('vehicles_list'))
    v = Vehicle.query.get_or_404(vehicle_id)
    if request.method == 'POST':
        v.plate_number = request.form['plate_number'].strip().upper()
        v.vehicle_type = request.form['vehicle_type']
        v.capacity_kg = float(request.form.get('capacity_kg') or 0)
        v.odometer_km = float(request.form.get('odometer_km') or 0)
        if request.form.get('status') and current_role() == 'fleet_manager':
            v.status = request.form['status']
        db.session.commit()
        flash('Vehicle updated.', 'success')
        return redirect(url_for('vehicles_list'))
    return render_template('vehicle_form.html', vehicle=v)


@app.route('/vehicles/<int:vehicle_id>/delete', methods=['POST'])
def vehicle_delete(vehicle_id):
    if not require_role('fleet_manager'):
        flash('Only Fleet Managers can delete vehicles.', 'error')
        return redirect(url_for('vehicles_list'))
    v = Vehicle.query.get_or_404(vehicle_id)
    if Trip.query.filter_by(vehicle_id=v.id).filter(Trip.status.in_(['scheduled', 'ongoing'])).count() > 0:
        flash('Cannot delete vehicle with active trips.', 'error')
        return redirect(url_for('vehicles_list'))
    db.session.delete(v)
    db.session.commit()
    flash('Vehicle removed.', 'success')
    return redirect(url_for('vehicles_list'))


# ---------------------------------------------------------------------------
# DRIVERS
# ---------------------------------------------------------------------------

@app.route('/drivers')
def drivers_list():
    if not require_role():
        return redirect(url_for('login'))
    drivers = Driver.query.order_by(Driver.id.desc()).all()
    return render_template('drivers.html', drivers=drivers, today=date.today())


@app.route('/drivers/new', methods=['GET', 'POST'])
def driver_new():
    if not require_role('fleet_manager', 'safety_officer'):
        flash('Only Fleet Managers or Safety Officers can add drivers.', 'error')
        return redirect(url_for('drivers_list'))
    if request.method == 'POST':
        try:
            d = Driver(
                name=request.form['name'].strip(),
                phone=request.form.get('phone', ''),
                license_number=request.form['license_number'].strip().upper(),
                license_expiry=datetime.strptime(request.form['license_expiry'], '%Y-%m-%d').date(),
                safety_score=int(request.form.get('safety_score') or 100),
                status='available',
            )
            db.session.add(d)
            db.session.commit()
            flash(f'Driver {d.name} added successfully.', 'success')
            return redirect(url_for('drivers_list'))
        except Exception as e:
            db.session.rollback()
            flash(f'Error adding driver: {e}', 'error')
    return render_template('driver_form.html', driver=None)


@app.route('/drivers/<int:driver_id>/edit', methods=['GET', 'POST'])
def driver_edit(driver_id):
    if not require_role('fleet_manager', 'safety_officer'):
        flash('Only Fleet Managers or Safety Officers can edit drivers.', 'error')
        return redirect(url_for('drivers_list'))
    d = Driver.query.get_or_404(driver_id)
    if request.method == 'POST':
        d.name = request.form['name'].strip()
        d.phone = request.form.get('phone', '')
        d.license_number = request.form['license_number'].strip().upper()
        d.license_expiry = datetime.strptime(request.form['license_expiry'], '%Y-%m-%d').date()
        if current_role() == 'safety_officer':
            d.safety_score = int(request.form.get('safety_score') or d.safety_score)
        if request.form.get('status'):
            d.status = request.form['status']
        db.session.commit()
        flash('Driver updated.', 'success')
        return redirect(url_for('drivers_list'))
    return render_template('driver_form.html', driver=d)


@app.route('/drivers/<int:driver_id>/delete', methods=['POST'])
def driver_delete(driver_id):
    if not require_role('fleet_manager', 'safety_officer'):
        flash('Only Fleet Managers or Safety Officers can delete drivers.', 'error')
        return redirect(url_for('drivers_list'))
    d = Driver.query.get_or_404(driver_id)
    if Trip.query.filter_by(driver_id=d.id).filter(Trip.status.in_(['scheduled', 'ongoing'])).count() > 0:
        flash('Cannot delete driver with active trips.', 'error')
        return redirect(url_for('drivers_list'))
    db.session.delete(d)
    db.session.commit()
    flash('Driver removed.', 'success')
    return redirect(url_for('drivers_list'))


# ---------------------------------------------------------------------------
# TRIPS / DISPATCH
# ---------------------------------------------------------------------------

@app.route('/trips')
def trips_list():
    if not require_role():
        return redirect(url_for('login'))
    trips = Trip.query.order_by(Trip.start_time.desc()).all()
    return render_template('trips.html', trips=trips)


@app.route('/trips/new', methods=['GET', 'POST'])
def trip_new():
    if not require_role('fleet_manager', 'driver'):
        flash('Only Fleet Managers or Drivers can create trips.', 'error')
        return redirect(url_for('trips_list'))

    vehicles = Vehicle.query.all()
    drivers = Driver.query.all()

    if request.method == 'POST':
        errors = []
        try:
            vehicle_id = int(request.form['vehicle_id'])
            driver_id = int(request.form['driver_id'])
            start = parse_dt(request.form['start_time'])
            end = parse_dt(request.form['end_time'])
            origin = request.form['origin'].strip()
            destination = request.form['destination'].strip()
            distance_km = float(request.form.get('distance_km') or 0)

            vehicle = Vehicle.query.get(vehicle_id)
            driver = Driver.query.get(driver_id)

            if end <= start:
                errors.append('End time must be after start time.')

            if vehicle is None:
                errors.append('Vehicle not found.')
            elif vehicle.status == 'maintenance':
                errors.append(f'Vehicle {vehicle.plate_number} is under maintenance and cannot be dispatched.')
            elif vehicle.status == 'inactive':
                errors.append(f'Vehicle {vehicle.plate_number} is inactive.')

            if driver is None:
                errors.append('Driver not found.')
            else:
                if driver.license_expired():
                    errors.append(f'Driver {driver.name} has an expired license ({driver.license_expiry}). Cannot assign.')
                if driver.status == 'off_duty':
                    errors.append(f'Driver {driver.name} is currently off duty.')

            if vehicle and not errors:
                clash = overlapping_trips_for_vehicle(vehicle_id, start, end)
                if clash:
                    errors.append(f'Vehicle {vehicle.plate_number} is already booked for an overlapping trip (Trip #{clash[0].id}).')

            if driver and not errors:
                clash = overlapping_trips_for_driver(driver_id, start, end)
                if clash:
                    errors.append(f'Driver {driver.name} is already booked for an overlapping trip (Trip #{clash[0].id}).')

            if errors:
                for e in errors:
                    flash(e, 'error')
                return render_template('trip_form.html', trip=None, vehicles=vehicles, drivers=drivers, form=request.form)

            trip = Trip(
                vehicle_id=vehicle_id,
                driver_id=driver_id,
                origin=origin,
                destination=destination,
                start_time=start,
                end_time=end,
                distance_km=distance_km,
                status='scheduled',
                notes=request.form.get('notes', ''),
            )
            db.session.add(trip)
            vehicle.status = 'on_trip'
            driver.status = 'on_trip'
            db.session.commit()
            flash(f'Trip #{trip.id} scheduled: {origin} \u2192 {destination}.', 'success')
            return redirect(url_for('trips_list'))
        except Exception as e:
            db.session.rollback()
            flash(f'Error creating trip: {e}', 'error')

    return render_template('trip_form.html', trip=None, vehicles=vehicles, drivers=drivers, form=None)


@app.route('/trips/<int:trip_id>/status', methods=['POST'])
def trip_update_status(trip_id):
    if not require_role('fleet_manager', 'driver'):
        flash('Not authorized.', 'error')
        return redirect(url_for('trips_list'))
    trip = Trip.query.get_or_404(trip_id)
    new_status = request.form.get('status')
    if new_status not in ('ongoing', 'completed', 'cancelled'):
        flash('Invalid status.', 'error')
        return redirect(url_for('trips_list'))

    trip.status = new_status
    vehicle = Vehicle.query.get(trip.vehicle_id)
    driver = Driver.query.get(trip.driver_id)

    if new_status == 'completed':
        if vehicle:
            vehicle.odometer_km = (vehicle.odometer_km or 0) + (trip.distance_km or 0)
            vehicle.status = 'available'
        if driver:
            driver.status = 'available'
    elif new_status == 'cancelled':
        if vehicle:
            vehicle.status = 'available'
        if driver:
            driver.status = 'available'
    elif new_status == 'ongoing':
        pass  # vehicle/driver already on_trip

    db.session.commit()
    flash(f'Trip #{trip.id} marked as {new_status}.', 'success')
    return redirect(url_for('trips_list'))


# ---------------------------------------------------------------------------
# MAINTENANCE
# ---------------------------------------------------------------------------

@app.route('/maintenance')
def maintenance_list():
    if not require_role():
        return redirect(url_for('login'))
    records = Maintenance.query.order_by(Maintenance.scheduled_date.desc()).all()
    return render_template('maintenance.html', records=records)


@app.route('/maintenance/new', methods=['GET', 'POST'])
def maintenance_new():
    if not require_role('fleet_manager'):
        flash('Only Fleet Managers can schedule maintenance.', 'error')
        return redirect(url_for('maintenance_list'))
    vehicles = Vehicle.query.all()
    if request.method == 'POST':
        try:
            vehicle_id = int(request.form['vehicle_id'])
            vehicle = Vehicle.query.get(vehicle_id)
            if vehicle is None:
                flash('Vehicle not found.', 'error')
                return redirect(url_for('maintenance_new'))

            active_trip = Trip.query.filter_by(vehicle_id=vehicle_id).filter(Trip.status.in_(['scheduled', 'ongoing'])).first()
            if active_trip:
                flash(f'Vehicle {vehicle.plate_number} has an active trip (#{active_trip.id}) and cannot be sent to maintenance.', 'error')
                return redirect(url_for('maintenance_new'))

            m = Maintenance(
                vehicle_id=vehicle_id,
                maintenance_type=request.form['maintenance_type'],
                scheduled_date=datetime.strptime(request.form['scheduled_date'], '%Y-%m-%d').date(),
                cost=float(request.form.get('cost') or 0),
                description=request.form.get('description', ''),
                status='scheduled',
                next_due_days=int(request.form.get('next_due_days') or 90),
            )
            db.session.add(m)
            vehicle.status = 'maintenance'
            db.session.commit()
            flash(f'Maintenance scheduled for {vehicle.plate_number}.', 'success')
            return redirect(url_for('maintenance_list'))
        except Exception as e:
            db.session.rollback()
            flash(f'Error scheduling maintenance: {e}', 'error')
    return render_template('maintenance_form.html', vehicles=vehicles)


@app.route('/maintenance/<int:m_id>/complete', methods=['POST'])
def maintenance_complete(m_id):
    if not require_role('fleet_manager'):
        flash('Only Fleet Managers can complete maintenance.', 'error')
        return redirect(url_for('maintenance_list'))
    m = Maintenance.query.get_or_404(m_id)
    m.status = 'completed'
    m.completed_date = date.today()
    vehicle = Vehicle.query.get(m.vehicle_id)
    if vehicle:
        vehicle.status = 'available'
        vehicle.last_maintenance_date = date.today()
        vehicle.next_maintenance_due = date.today() + timedelta(days=m.next_due_days or 90)
    db.session.commit()
    flash(f'Maintenance #{m.id} marked completed. Vehicle back in service.', 'success')
    return redirect(url_for('maintenance_list'))


# ---------------------------------------------------------------------------
# EXPENSES / FUEL LOGS
# ---------------------------------------------------------------------------

@app.route('/expenses')
def expenses_list():
    if not require_role():
        return redirect(url_for('login'))
    expenses = Expense.query.order_by(Expense.expense_date.desc()).all()
    total = sum(e.amount for e in expenses)
    by_category = {}
    for e in expenses:
        by_category[e.category] = by_category.get(e.category, 0) + e.amount
    return render_template('expenses.html', expenses=expenses, total=total, by_category=by_category)


@app.route('/expenses/new', methods=['GET', 'POST'])
def expense_new():
    if not require_role('fleet_manager', 'driver', 'financial_analyst'):
        flash('Not authorized to log expenses.', 'error')
        return redirect(url_for('expenses_list'))
    vehicles = Vehicle.query.all()
    trips = Trip.query.order_by(Trip.id.desc()).limit(50).all()
    if request.method == 'POST':
        try:
            trip_id = request.form.get('trip_id') or None
            e = Expense(
                vehicle_id=int(request.form['vehicle_id']),
                trip_id=int(trip_id) if trip_id else None,
                category=request.form['category'],
                amount=float(request.form['amount']),
                liters=float(request.form['liters']) if request.form.get('liters') else None,
                expense_date=datetime.strptime(request.form['expense_date'], '%Y-%m-%d').date(),
                notes=request.form.get('notes', ''),
            )
            db.session.add(e)
            db.session.commit()
            flash('Expense logged.', 'success')
            return redirect(url_for('expenses_list'))
        except Exception as ex:
            db.session.rollback()
            flash(f'Error logging expense: {ex}', 'error')
    return render_template('expense_form.html', vehicles=vehicles, trips=trips)


# ---------------------------------------------------------------------------
# SEED DATA
# ---------------------------------------------------------------------------

def seed_data():
    if Vehicle.query.count() > 0:
        return
    v1 = Vehicle(plate_number='GJ01AB1234', vehicle_type='Truck', capacity_kg=5000, odometer_km=42000, status='available',
                 last_maintenance_date=date.today() - timedelta(days=60), next_maintenance_due=date.today() + timedelta(days=30))
    v2 = Vehicle(plate_number='GJ01CD5678', vehicle_type='Van', capacity_kg=1200, odometer_km=18500, status='available',
                 last_maintenance_date=date.today() - timedelta(days=85), next_maintenance_due=date.today() + timedelta(days=5))
    v3 = Vehicle(plate_number='GJ05EF9012', vehicle_type='Truck', capacity_kg=7500, odometer_km=95000, status='available',
                 last_maintenance_date=date.today() - timedelta(days=95), next_maintenance_due=date.today() - timedelta(days=3))
    v4 = Vehicle(plate_number='GJ09XY4321', vehicle_type='Mini Truck', capacity_kg=2000, odometer_km=5200, status='inactive')
    db.session.add_all([v1, v2, v3, v4])

    d1 = Driver(name='Ramesh Patel', phone='9820011122', license_number='DL-GJ-001', license_expiry=date.today() + timedelta(days=200), safety_score=92, status='available')
    d2 = Driver(name='Suresh Yadav', phone='9820033344', license_number='DL-GJ-002', license_expiry=date.today() + timedelta(days=15), safety_score=78, status='available')
    d3 = Driver(name='Anita Shah', phone='9820055566', license_number='DL-GJ-003', license_expiry=date.today() - timedelta(days=10), safety_score=85, status='available')
    d4 = Driver(name='Karan Mehta', phone='9820077788', license_number='DL-GJ-004', license_expiry=date.today() + timedelta(days=365), safety_score=97, status='available')
    db.session.add_all([d1, d2, d3, d4])
    db.session.commit()

    t1 = Trip(vehicle_id=v1.id, driver_id=d1.id, origin='Ahmedabad', destination='Surat',
              start_time=datetime.now() - timedelta(days=3), end_time=datetime.now() - timedelta(days=3) + timedelta(hours=5),
              distance_km=270, status='completed', notes='Regular delivery run')
    t2 = Trip(vehicle_id=v2.id, driver_id=d4.id, origin='Ahmedabad', destination='Vadodara',
              start_time=datetime.now() - timedelta(days=1), end_time=datetime.now() - timedelta(days=1) + timedelta(hours=2),
              distance_km=110, status='completed', notes='')
    t3 = Trip(vehicle_id=v1.id, driver_id=d1.id, origin='Ahmedabad', destination='Rajkot',
              start_time=datetime.now() + timedelta(hours=3), end_time=datetime.now() + timedelta(hours=9),
              distance_km=215, status='scheduled', notes='Upcoming dispatch')
    db.session.add_all([t1, t2, t3])
    db.session.commit()

    m1 = Maintenance(vehicle_id=v3.id, maintenance_type='Engine Service', scheduled_date=date.today() - timedelta(days=95),
                      completed_date=date.today() - timedelta(days=93), cost=4500, description='Full engine service', status='completed', next_due_days=90)
    db.session.add(m1)

    exp_data = [
        (v1.id, t1.id, 'fuel', 2400, 45, date.today() - timedelta(days=3)),
        (v2.id, t2.id, 'fuel', 950, 18, date.today() - timedelta(days=1)),
        (v1.id, None, 'toll', 320, None, date.today() - timedelta(days=3)),
        (v3.id, None, 'maintenance', 4500, None, date.today() - timedelta(days=93)),
        (v1.id, None, 'fuel', 2200, 42, date.today() - timedelta(days=35)),
        (v2.id, None, 'fuel', 1100, 20, date.today() - timedelta(days=40)),
        (v1.id, None, 'other', 500, None, date.today() - timedelta(days=20)),
    ]
    for vid, tid, cat, amt, liters, edate in exp_data:
        db.session.add(Expense(vehicle_id=vid, trip_id=tid, category=cat, amount=amt, liters=liters, expense_date=edate))
    db.session.commit()


with app.app_context():
    db.create_all()
    seed_data()


if __name__ == '__main__':
    app.run(debug=True, host='0.0.0.0', port=5000)
