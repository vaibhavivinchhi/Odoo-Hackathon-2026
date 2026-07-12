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



// CREATE VEHICLE TABLE

mysqli_query($conn,

"CREATE TABLE IF NOT EXISTS vehicles(

id INT AUTO_INCREMENT PRIMARY KEY,

registration VARCHAR(50),

vehicle_name VARCHAR(100),

type VARCHAR(50),

capacity VARCHAR(50),

status VARCHAR(50),

created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP

)"

);



// ADD VEHICLE


if(isset($_POST['addVehicle'])){


$registration=$_POST['registration'];

$name=$_POST['name'];

$type=$_POST['type'];

$capacity=$_POST['capacity'];

$status=$_POST['status'];



mysqli_query($conn,

"INSERT INTO vehicles

(registration,vehicle_name,type,capacity,status)

VALUES

('$registration','$name','$type','$capacity','$status')"

);


header("Location:vehicles.php");

exit();


}



// FETCH VEHICLES


$result=mysqli_query($conn,

"SELECT * FROM vehicles"

);


?>



<!DOCTYPE html>

<html lang="en">

<head>


<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">


<title>TransitOps Vehicles</title>


<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">


<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">



<style>


body{

background:#f4f7fb;

}


.sidebar{

width:230px;

position:fixed;

height:100vh;

background:#0f172a;

color:white;

padding:20px;

}



.sidebar a{

display:block;

color:white;

text-decoration:none;

padding:10px;

margin:6px 0;

border-radius:8px;

}


.sidebar a:hover{

background:#2563eb;

}



.content{

margin-left:250px;

padding:25px;

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



<a href="logout.php">

<i class="fa fa-sign-out"></i>
Logout

</a>



</div>





<div class="content">


<div class="d-flex justify-content-between mb-3">


<h2>
Vehicle Registry
</h2>



<button class="btn btn-primary"
data-bs-toggle="modal"
data-bs-target="#vehicleModal">

<i class="fa fa-plus"></i>
Add Vehicle

</button>


</div>





<div class="card p-3">


<table class="table table-hover">


<thead>

<tr>

<th>Registration</th>

<th>Name</th>

<th>Type</th>

<th>Capacity</th>

<th>Status</th>


</tr>


</thead>



<tbody>



<?php while($row=mysqli_fetch_assoc($result)){ ?>


<tr>


<td>
<?php echo $row['registration']; ?>
</td>


<td>
<?php echo $row['vehicle_name']; ?>
</td>


<td>
<?php echo $row['type']; ?>
</td>


<td>
<?php echo $row['capacity']; ?>
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







<!-- ADD VEHICLE MODAL -->


<div class="modal fade" id="vehicleModal">


<div class="modal-dialog">


<div class="modal-content">


<form method="POST">


<div class="modal-header">

<h5>
Add Vehicle
</h5>


<button class="btn-close"
data-bs-dismiss="modal"></button>


</div>




<div class="modal-body">



<input 
name="registration"
class="form-control mb-2"
placeholder="Registration"
required>



<input 
name="name"
class="form-control mb-2"
placeholder="Vehicle Name"
required>



<input 
name="type"
class="form-control mb-2"
placeholder="Type"
required>




<input 
name="capacity"
class="form-control mb-2"
placeholder="Capacity"
required>




<select 
name="status"
class="form-select">


<option>
Available
</option>


<option>
On Trip
</option>


<option>
In Shop
</option>


<option>
Retired
</option>


</select>



</div>





<div class="modal-footer">


<button 
class="btn btn-success"
name="addVehicle">

Save

</button>


</div>



</form>


</div>


</div>


</div>





<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>


</body>

</html>