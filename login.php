<?php

session_start();


// Connect Database

$conn = mysqli_connect(
    "localhost",
    "root",
    "",
    "transitops_db"
);


if(!$conn){

    die("Database Connection Failed");

}



$message="";


// Login Process

if(isset($_POST['login'])){


    $email = $_POST['email'];

    $password = $_POST['password'];



    // Check User

    $query = mysqli_query($conn,

    "SELECT * FROM users 

    WHERE email='$email' 

    AND password='$password'"

    );



    if(mysqli_num_rows($query)>0){


        $user=mysqli_fetch_assoc($query);


        $_SESSION['user_id']=$user['id'];

        $_SESSION['name']=$user['name'];

        $_SESSION['email']=$user['email'];

        $_SESSION['role']=$user['role'];



        header("Location:dashboard.php");

        exit();


    }

    else{


        $message="Invalid Email or Password";


    }


}


?>



<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">


<title>TransitOps Login</title>


<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">


<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">


<style>


body{

height:100vh;

background:linear-gradient(135deg,#2563eb,#0f172a);

display:flex;

justify-content:center;

align-items:center;

font-family:Poppins,sans-serif;

}



.login-card{

width:420px;

padding:40px;

background:white;

border-radius:20px;

box-shadow:0 15px 35px rgba(0,0,0,.3);

}



.logo{

text-align:center;

margin-bottom:25px;

}


.logo i{

font-size:60px;

color:#2563eb;

}


.form-control{

height:50px;

border-radius:10px;

}


.btn-login{

width:100%;

height:50px;

background:#10b981;

color:white;

border:none;

border-radius:10px;

font-weight:bold;

}


.btn-login:hover{

background:#059669;

}


</style>


</head>



<body>



<div class="login-card">


<div class="logo">


<i class="fa-solid fa-truck-fast"></i>


<h2>TransitOps</h2>


<p>
Smart Transport Operations Platform
</p>


</div>



<h5 class="text-danger text-center">

<?php echo $message; ?>

</h5>



<form method="POST">


<div class="mb-3">


<input

type="email"

name="email"

class="form-control"

placeholder="Email Address"

required>


</div>




<div class="mb-3">


<input

type="password"

name="password"

class="form-control"

placeholder="Password"

required>


</div>



<button

class="btn-login"

name="login">


Login


</button>



</form>




<p class="text-center mt-3">


Don't have account?


<a href="register.php">

Register

</a>


</p>



</div>



</body>

</html>