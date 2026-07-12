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



// CREATE DRIVERS TABLE


mysqli_query($conn,

"CREATE TABLE IF NOT EXISTS drivers(

id INT AUTO_INCREMENT PRIMARY KEY,

name VARCHAR(100),

license_no VARCHAR(50),

category VARCHAR(50),

expiry DATE,

safety_score INT,

status VARCHAR(50),

created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP

)"

);





// ADD DRIVER


if(isset($_POST['addDriver'])){


$name=$_POST['name'];

$license=$_POST['license'];

$category=$_POST['category'];

$expiry=$_POST['expiry'];

$score=$_POST['score'];

$status=$_POST['status'];



mysqli_query($conn,

"INSERT INTO drivers

(name,license_no,category,expiry,safety_score,status)

VALUES

('$name','$license','$category','$expiry','$score','$status')"

);



header("Location:drivers.php");

exit();


}





// FETCH DRIVERS


$result=mysqli_query($conn,

"SELECT * FROM drivers"

);


?>



<!DOCTYPE html>

<html lang="en">

<head>


<meta charset="UTF-8">

<meta name="viewport" content="width=device-width,initial-scale=1">


<title>TransitOps - Drivers</title>



<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">


<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">


<style>


body{

background:#f4f7fb;

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



<a href="logout.php" class="btn btn-danger mt-3">

Logout

</a>



</div>






<div class="content">


<div class="d-flex justify-content-between align-items-center mb-3">


<h2>
Driver Management
</h2>


<button class="btn btn-primary"
data-bs-toggle="modal"
data-bs-target="#driverModal">


<i class="fa fa-plus"></i>
Add Driver


</button>


</div>






<div class="card p-3">


<table class="table table-hover">


<thead>

<tr>

<th>Name</th>

<th>License No</th>

<th>Category</th>

<th>Expiry</th>

<th>Safety</th>

<th>Status</th>


</tr>


</thead>




<tbody>



<?php while($row=mysqli_fetch_assoc($result)){ ?>


<tr>


<td>

<?php echo $row['name']; ?>

</td>


<td>

<?php echo $row['license_no']; ?>

</td>


<td>

<?php echo $row['category']; ?>

</td>


<td>

<?php echo $row['expiry']; ?>

</td>


<td>

<?php echo $row['safety_score']; ?>

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








<!-- ADD DRIVER MODAL -->


<div class="modal fade" id="driverModal">


<div class="modal-dialog">


<div class="modal-content">


<form method="POST">


<div class="modal-header">


<h5>Add Driver</h5>


<button class="btn-close"
data-bs-dismiss="modal"></button>


</div>





<div class="modal-body">


<input 
name="name"
class="form-control mb-2"
placeholder="Driver Name"
required>



<input 
name="license"
class="form-control mb-2"
placeholder="License Number"
required>



<input 
name="category"
class="form-control mb-2"
placeholder="Category"
required>



<input 
name="expiry"
type="date"
class="form-control mb-2"
required>



<input 
name="score"
type="number"
class="form-control mb-2"
placeholder="Safety Score"
required>




<select name="status" class="form-select">


<option>
Available
</option>


<option>
On Trip
</option>


<option>
Off Duty
</option>


<option>
Suspended
</option>


</select>


</div>





<div class="modal-footer">


<button 
class="btn btn-success"
name="addDriver">


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