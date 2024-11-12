
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>Login</title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" type="text/css" href="Assets/css/login.css">
    </head>
    <body>
        <div id="diagonal-border"></div>
        <div class="loginform">
            <img class="logo" src="Assets/images/logo.png">
            <h1>Sto. Niño Preparatory
                School of Mohon,
                Talisay City, Inc.
            </h1>
            <form action="login.php" method="post">
            <div class="inputs">
                <?php if (isset($_GET['error'])) { ?>
     		    <p class="error"><?php echo $_GET['error']; ?></p>
     	        <?php } ?>
                <div class="inputBox">
                <input type="text" id="username" name="username" required>
                <span>Username</span>
                </div>
                <div class="inputBox">
                <input type="password" id="password" name="password" required>
                <span>Password</span>
                </div>
                <button>Sign In</button>
                <p id="forgotpass">Forgot Password?</p>
                <p id="contact_it">Contact your IT Coordinator for password reset requests.</p>
            </div>
            </form>
        </div>

        <script src="" async defer></script>
    </body>
</html>
