<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>TransitOps - Fuel & Expense</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
<style>
body{background:#f4f7fb}.sidebar{position:fixed;left:0;top:0;width:230px;height:100vh;background:#0f172a;padding:20px}
.sidebar h3,.sidebar a{color:#fff}.sidebar a{display:block;text-decoration:none;padding:10px;border-radius:8px;margin:6px 0}.sidebar a:hover{background:#2563eb}
.content{margin-left:250px;padding:24px}.card{border:none;border-radius:12px}
</style>
</head>
<body>
<div class="sidebar">
<h3>🚛 TransitOps</h3>
<a href="dashboard.html"><i class="fa fa-chart-line"></i> Dashboard</a>
<a href="vehicles.html"><i class="fa fa-truck"></i> Vehicles</a>
<a href="drivers.html"><i class="fa fa-user"></i> Drivers</a>
<a href="trip_management.html"><i class="fa fa-route"></i> Trips</a>
<a href="maintenance.html"><i class="fa fa-tools"></i> Maintenance</a>
<a href="fuel_expense.html"><i class="fa fa-gas-pump"></i> Fuel & Expense</a>
</div>

<div class="content">
<div class="d-flex justify-content-between align-items-center mb-3">
<h2>Fuel & Expense Management</h2>
<button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#entryModal">
<i class="fa fa-plus"></i> Add Entry
</button>
</div>

<div class="row mb-3">
<div class="col-md-3"><div class="card p-3 text-center"><h4 id="fuelCost">₹6500</h4><small>Total Fuel Cost</small></div></div>
<div class="col-md-3"><div class="card p-3 text-center"><h4 id="maintCost">₹8200</h4><small>Maintenance Cost</small></div></div>
<div class="col-md-3"><div class="card p-3 text-center"><h4 id="otherCost">₹1200</h4><small>Other Expenses</small></div></div>
<div class="col-md-3"><div class="card p-3 text-center"><h4 id="totalCost">₹15900</h4><small>Total Operational Cost</small></div></div>
</div>

<div class="card p-3 mb-3">
<input id="search" class="form-control" placeholder="Search by vehicle">
</div>

<div class="card p-3">
<table class="table table-hover" id="expenseTable">
<thead>
<tr>
<th>Vehicle</th>
<th>Date</th>
<th>Fuel (L)</th>
<th>Fuel Cost</th>
<th>Expense Type</th>
<th>Expense Cost</th>
</tr>
</thead>
<tbody>
<tr><td>Van-05</td><td>2026-07-12</td><td>45</td><td>₹2700</td><td>Toll</td><td>₹300</td></tr>
<tr><td>Truck-12</td><td>2026-07-11</td><td>60</td><td>₹3800</td><td>Repair</td><td>₹2500</td></tr>
</tbody>
</table>
</div>
</div>

<div class="modal fade" id="entryModal">
<div class="modal-dialog">
<div class="modal-content">
<div class="modal-header">
<h5>Add Fuel / Expense</h5>
<button class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body">
<input id="vehicle" class="form-control mb-2" placeholder="Vehicle">
<input id="date" type="date" class="form-control mb-2">
<input id="liters" type="number" class="form-control mb-2" placeholder="Fuel (Liters)">
<input id="fuel" type="number" class="form-control mb-2" placeholder="Fuel Cost">
<select id="etype" class="form-select mb-2">
<option>Toll</option>
<option>Maintenance</option>
<option>Repair</option>
<option>Parking</option>
<option>Other</option>
</select>
<input id="ecost" type="number" class="form-control" placeholder="Expense Cost">
</div>
<div class="modal-footer">
<button class="btn btn-success" onclick="addEntry()" data-bs-dismiss="modal">Save</button>
</div>
</div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function addEntry(){
const row=document.querySelector("#expenseTable tbody").insertRow();
row.innerHTML=`<td>${vehicle.value}</td><td>${date.value}</td><td>${liters.value}</td><td>₹${fuel.value}</td><td>${etype.value}</td><td>₹${ecost.value}</td>`;
}
search.onkeyup=function(){
const q=this.value.toLowerCase();
document.querySelectorAll("#expenseTable tbody tr").forEach(r=>{
r.style.display=r.innerText.toLowerCase().includes(q)?"":"none";
});
}
</script>
</body>
</html>