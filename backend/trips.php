<?php

session_start();


if(!isset($_SESSION['email'])){

    header("Location:login.php");
    exit();

}



// DATABASE CONNECTION

$conn=mysqli_connect(
    "localhost",
    "root",
    "",
    "transitops_db"
);



if(!$conn){

    die("Database Connection Failed");

}




// CREATE TRIPS TABLE


mysqli_query($conn,

"CREATE TABLE IF NOT EXISTS trips(

id INT AUTO_INCREMENT PRIMARY KEY,

trip_id VARCHAR(50),

vehicle VARCHAR(100),

driver VARCHAR(100),

source VARCHAR(100),

destination VARCHAR(100),

cargo VARCHAR(50),

status VARCHAR(50),

created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP

)"

);






// ADD TRIP


if(isset($_POST['addTrip'])){


$trip_id=$_POST['trip_id'];

$vehicle=$_POST['vehicle'];

$driver=$_POST['driver'];

$source=$_POST['source'];

$destination=$_POST['destination'];

$cargo=$_POST['cargo'];

$status=$_POST['status'];




mysqli_query($conn,

"INSERT INTO trips

(trip_id,vehicle,driver,source,destination,cargo,status)

VALUES

('$trip_id','$vehicle','$driver','$source','$destination','$cargo','$status')"

);



header("Location:trips.php");

exit();


}






// GET TRIPS


$result=mysqli_query($conn,

"SELECT * FROM trips"

);



?>



<!DOCTYPE html>
<html lang="en">

<head>


<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">


<title>TransitOps - Trip Management</title>



<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">


<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">



<style>


body{

background:#f5f7fb;

}



.sidebar{

position:fixed;

left:0;

top:0;

width:230px;

height:100vh;

background:#0f172a;

padding:20px;

}



.sidebar h3{

color:white;

}



.sidebar a{

display:block;

color:white;

text-decoration:none;

padding:10px;

border-radius:8px;

margin:6px 0;

}



.sidebar a:hover{

background:#2563eb;

}



.content{

margin-left:250px;

padding:24px;

}



.card{

border:none;

border-radius:12px;

}



</style>


</head>




<body>



<div class="sidebar">


<h3>
🚛 TransitOps
</h3>



<a href="dashboard.php">

<i class="fa fa-chart-line"></i>
Dashboard

</a>




<a href="vehicles.php">

<i class="fa fa-truck"></i>
Vehicles

</a>




<a href="drivers.php">

<i class="fa fa-user"></i>
Drivers

</a>




<a href="trips.php">

<i class="fa fa-route"></i>
Trips

</a>



<a href="logout.php" class="btn btn-danger mt-3">

Logout

</a>


</div>







<div class="content">



<div class="d-flex justify-content-between mb-3">


<h2>
Trip Management
</h2>



<button class="btn btn-primary"
data-bs-toggle="modal"
data-bs-target="#tripModal">


<i class="fa fa-plus"></i>

Create Trip


</button>


</div>








<div class="card p-3">


<table class="table table-hover">


<thead>


<tr>

<th>Trip ID</th>

<th>Vehicle</th>

<th>Driver</th>

<th>Source</th>

<th>Destination</th>

<th>Cargo</th>

<th>Status</th>


</tr>


</thead>




<tbody>


<?php while($row=mysqli_fetch_assoc($result)){ ?>


<tr>


<td>
<?php echo $row['trip_id']; ?>
</td>



<td>
<?php echo $row['vehicle']; ?>
</td>



<td>
<?php echo $row['driver']; ?>
</td>



<td>
<?php echo $row['source']; ?>
</td>



<td>
<?php echo $row['destination']; ?>
</td>



<td>
<?php echo $row['cargo']; ?>
</td>



<td>

<span class="badge bg-success">

<?php echo $row['status']; ?>

</span>

</td>



</tr>



<?php } ?>



</tbody>



</table>


</div>



</div>









<!-- CREATE TRIP MODAL -->


<div class="modal fade" id="tripModal">


<div class="modal-dialog modal-lg">


<div class="modal-content">



<form method="POST">



<div class="modal-header">


<h5>
Create Trip
</h5>


<button class="btn-close"
data-bs-dismiss="modal"></button>


</div>





<div class="modal-body">


<div class="row g-2">



<div class="col-md-6">

<input 
name="trip_id"
class="form-control"
placeholder="Trip ID"
required>

</div>



<div class="col-md-6">

<input 
name="vehicle"
class="form-control"
placeholder="Vehicle"
required>

</div>




<div class="col-md-6">

<input 
name="driver"
class="form-control"
placeholder="Driver"
required>

</div>




<div class="col-md-6">

<input 
name="cargo"
class="form-control"
placeholder="Cargo Weight"
required>

</div>





<div class="col-md-6">

<input 
name="source"
class="form-control"
placeholder="Source"
required>

</div>





<div class="col-md-6">

<input 
name="destination"
class="form-control"
placeholder="Destination"
required>

</div>




<div class="col-md-12">


<select name="status" class="form-select">


<option>
Draft
</option>


<option>
Dispatched
</option>


<option>
Completed
</option>


<option>
Cancelled
</option>


</select>


</div>


</div>


</div>







<div class="modal-footer">


<button 
class="btn btn-success"
name="addTrip">

Save Trip

</button>


</div>



</form>


</div>


</div>


</div>







<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>



</body>

</html>