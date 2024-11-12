<?php
session_start();

if (isset($_SESSION['id']) && isset($_SESSION['user_name'])) {

 ?>
<!DOCTYPE html>

<html>
    <head>
        <title>Admin</title>
        <link rel="stylesheet" href="">
    </head>
    <body>
       <h1>Welcome to the Teacher Dashboard</h1>
       <h1><?php echo $_SESSION['name']; ?></h1>
       <a href="../logout.php">Logout</a>
       <script src="" async defer></script>
    </body>
</html>

<?php
}else{
     header("Location: ../index.php");
     exit();
}
 ?>
