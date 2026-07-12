<?php

session_start();


// Check Login

if(!isset($_SESSION['email'])){

    header("Location:login.php");
    exit();

}


$name = $_SESSION['name'];
$email = $_SESSION['email'];
$role = $_SESSION['role'];

?>


<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">


<title>TransitOps Dashboard</title>


<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">


<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


<style>

body{
background:#eef3f8;
font-family:Arial,sans-serif;
}


.sidebar{

width:240px;

position:fixed;

top:0;

left:0;

height:100vh;

background:#0f172a;

color:white;

padding:20px;

}


.sidebar h3{

text-align:center;

margin-bottom:30px;

}



.sidebar a{

display:block;

color:white;

text-decoration:none;

padding:12px;

border-radius:8px;

margin-bottom:8px;

}



.sidebar a:hover{

background:#2563eb;

}



.content{

margin-left:260px;

padding:25px;

}



.card{

border:none;

border-radius:15px;

box-shadow:0 5px 15px rgba(0,0,0,.08);

}



.card i{

font-size:40px;

}



.profile{

background:white;

padding:15px;

border-radius:10px;

margin-bottom:20px;

}


</style>

</head>



<body>


<!-- SIDEBAR -->

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





<a href="maintenance.php">

<i class="fa fa-tools"></i>

Maintenance

</a>





<a href="fuel.php">

<i class="fa fa-gas-pump"></i>

Fuel

</a>





<a href="expenses.php">

<i class="fa fa-money-bill"></i>

Expenses

</a>





<a href="reports.php">

<i class="fa fa-chart-pie"></i>

Reports

</a>





<a href="logout.php" class="btn btn-danger mt-3">

<i class="fa fa-sign-out"></i>

Logout

</a>



</div>





<!-- CONTENT -->


<div class="content">



<div class="profile">


<h4>
Welcome, <?php echo $name; ?>
</h4>


<p>
Email:
<?php echo $email; ?>
</p>


<p>
Role:
<?php echo $role; ?>
</p>


</div>





<h2 class="mb-4">
Fleet Dashboard
</h2>




<div class="row">



<div class="col-md-3">

<div class="card p-4 text-center">

<i class="fa fa-truck text-primary"></i>

<h2>125</h2>

<p>Total Vehicles</p>

</div>

</div>




<div class="col-md-3">

<div class="card p-4 text-center">

<i class="fa fa-route text-success"></i>

<h2>18</h2>

<p>Active Trips</p>

</div>

</div>




<div class="col-md-3">

<div class="card p-4 text-center">

<i class="fa fa-user text-warning"></i>

<h2>87</h2>

<p>Drivers</p>

</div>

</div>




<div class="col-md-3">

<div class="card p-4 text-center">

<i class="fa fa-tools text-danger"></i>

<h2>7</h2>

<p>Maintenance</p>

</div>

</div>



</div>





<div class="row mt-5">


<div class="col-lg-7">


<div class="card p-4">


<h4>
Fleet Utilization
</h4>


<canvas id="fleetChart"></canvas>


</div>


</div>





<div class="col-lg-5">


<div class="card p-4">


<h4>
Vehicle Status
</h4>


<canvas id="statusChart"></canvas>


</div>


</div>


</div>






<div class="card mt-5 p-4">


<h4>
Recent Trips
</h4>



<table class="table">


<tr>

<th>Vehicle</th>

<th>Driver</th>

<th>Destination</th>

<th>Status</th>

</tr>



<tr>

<td>Van-05</td>

<td>Alex</td>

<td>Surat</td>

<td>

<span class="badge bg-success">

On Trip

</span>

</td>

</tr>




<tr>

<td>Truck-12</td>

<td>John</td>

<td>Vadodara</td>

<td>

<span class="badge bg-warning text-dark">

Pending

</span>

</td>

</tr>





<tr>

<td>Mini Van</td>

<td>Robert</td>

<td>Rajkot</td>

<td>

<span class="badge bg-danger">

Maintenance

</span>

</td>

</tr>



</table>


</div>



</div>






<script>


new Chart(document.getElementById('fleetChart'),{


type:'line',


data:{


labels:['Jan','Feb','Mar','Apr','May','Jun'],


datasets:[{


label:'Fleet Utilization %',


data:[65,70,75,80,86,90],


borderWidth:3


}]


}


});





new Chart(document.getElementById('statusChart'),{


type:'doughnut',


data:{


labels:['Available','On Trip','Maintenance'],


datasets:[{


data:[70,18,7]


}]


}


});



</script>


</body>

</html>