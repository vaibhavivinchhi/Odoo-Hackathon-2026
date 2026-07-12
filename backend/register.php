<?php

// MYSQL CONNECTION

$conn = mysqli_connect("localhost","root","");


if(!$conn){
    die("MySQL Connection Failed");
}


// CREATE DATABASE

mysqli_query($conn,
"CREATE DATABASE IF NOT EXISTS transitops_db"
);


// SELECT DATABASE

mysqli_select_db($conn,"transitops_db");


// CREATE TABLE

mysqli_query($conn,

"CREATE TABLE IF NOT EXISTS users(

id INT AUTO_INCREMENT PRIMARY KEY,

name VARCHAR(100),

email VARCHAR(100) UNIQUE,

phone VARCHAR(20),

role VARCHAR(50),

password VARCHAR(255),

created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP

)"

);



$message="";


// REGISTER DATA

if(isset($_POST['register'])){


$name=$_POST['name'];

$email=$_POST['email'];

$phone=$_POST['phone'];

$role=$_POST['role'];

$password=$_POST['password'];



// CHECK EMAIL

$check=mysqli_query($conn,

"SELECT * FROM users WHERE email='$email'"

);



if(mysqli_num_rows($check)>0){


$message="Email already exists";


}

else{


$sql=mysqli_query($conn,

"INSERT INTO users

(name,email,phone,role,password)

VALUES

('$name','$email','$phone','$role','$password')"

);



if($sql){

$message="Registration Successful";

}

else{

$message="Registration Failed";

}


}


}


?>



<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width,initial-scale=1">


<title>TransitOps Register</title>


<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">


<style>

body{

margin:0;

background:linear-gradient(135deg,#2563eb,#0f172a);

height:100vh;

display:flex;

align-items:center;

justify-content:center;

font-family:Poppins,sans-serif;

}


.wrapper{

width:900px;

background:white;

display:grid;

grid-template-columns:1fr 1fr;

border-radius:20px;

overflow:hidden;

}


.left{

background:#2563eb;

color:white;

padding:50px;

}


.right{

padding:40px;

}


.form-control,
.form-select{

height:48px;

border-radius:12px;

}


.btn-main{

background:#2563eb;

color:white;

width:100%;

height:50px;

border-radius:12px;

}


</style>


</head>



<body>


<div class="wrapper">


<div class="left">


<h1>
🚚 TransitOps
</h1>


<h4>
Smart Transport Operations
</h4>


<p>
Manage vehicles, drivers, trips and analytics.
</p>


</div>




<div class="right">


<h2>Create Account</h2>


<h5 style="color:green;">
<?php echo $message;?>
</h5>



<form method="POST">


<input 
class="form-control mb-3"
name="name"
placeholder="Full Name"
required>



<input 
class="form-control mb-3"
type="email"
name="email"
placeholder="Email"
required>



<input 
class="form-control mb-3"
name="phone"
placeholder="Phone"
required>



<select 
class="form-select mb-3"
name="role">


<option>
Fleet Manager
</option>


<option>
Driver
</option>


<option>
Safety Officer
</option>


<option>
Financial Analyst
</option>


</select>




<input

class="form-control mb-3"

type="password"

name="password"

placeholder="Password"

required>




<button

class="btn btn-main"

name="register">

Register

</button>



</form>



<p class="mt-3">

Already have account?

<a href="login.php">
Login
</a>


</p>



</div>


</div>



</body>

</html>