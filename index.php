<?php 
session_start();
$error = "";
if(isset($_GET["error"])){
    if ($_GET["error"] == "invalidlogin"){
        $error = "Invalid email or password";
    }
    if ($_GET["error"] == "emptyfields"){
        $error = "Please fill in all fields";
    }
    if ($_GET["error"] == "invalidemail"){
        $error = "Invalid email address";
    }
}
$registerError = "";



if(isset($_GET["registererror"])){
    if ($_GET["registererror"] == "emptyfields"){
        $registerError = "Please fill all fields";
    }
    if ($_GET["registererror"] == "invalidemail"){
        $registerError = "Invalid email address";
    }
    if ($_GET["registererror"] == "emailtaken") {
        $registerError = "Email already exists";
    }
}
?>

<html>

    <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
   
        <title>
            Velora 
        </title>
        <link rel="stylesheet" href="css/style.css?v=3">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>
    </head>                          
    <body>
        <header>
            <div id="logo">
                <img src="images/logo.png" alt="Velora Logo">
            </div>
            

            <input type="text" id="search" placeholder="Search Products...">
            
            <div id="icons">
                <i class="fa-solid fa-magnifying-glass" id="mobile-search-icon"></i>
                <?php if (isset($_SESSION["user"])){ ?>
                    <div id="user-menu">
                        <?php echo $_SESSION["user"]; ?>
                        <div id="user-dropdown">
                            <?php if (isset($_SESSION["role"]) && $_SESSION["role"] == 2): ?>
                                <a href="admin.php">Admin Panel</a>
                            <?php endif; ?>
                            <a href="logout.php">Logout</a>
                        </div>
                    </div>
                <?php } else { ?>
                    <div id="user-icon">
                        <i class="fa-solid fa-user"></i>
                    </div>
                <?php }?>
                <div id="cart-icon">
                    <i class="fa-solid fa-cart-shopping"></i>
                    <span id="cart-count">0</span>
                </div>
            </div>
        </header>
        <div id="login-dropdown" class="<?php echo !empty($error) ?'active': ''; ?>">
            <div class="login-top"></div>
                <div class="login-content">
                    <h2>Welcome!</h2>
                    <p>Enter in your account to continue</p>
                    <?php if(!empty($error)): ?>
                            <p class="error-message"><?php echo $error; ?></p>
                    <?php endif; ?> 
                    <form action="login.php" method="POST">
                        <input type="email" name="email" placeholder="Email" required>
                        <input type="password" name="password" placeholder="Password" required>
                        <button type="submit">Entry</button>
                        <p>Don't have accont?
                            <a href="#" id="open-register">Register here</a>
                        </p>
                    </form>
                </div>
        </div>
        <div id="register-dropdown" class="<?php echo !empty($registerError) ?'active': ''; ?>">
            <div class="login-top"></div>
            <div class="login-content">
                <h2>Create Account</h2>
                <?php if(!empty($registerError)): ?>
                            <p class="error-message"><?php echo $registerError; ?></p>
                <?php endif; ?> 
                <form action="register.php" method="POST">
                    <input type="text" name="username" placeholder="Username" required>
                    <input type="email" name="email" placeholder="Email" required>
                    <input type="password" name="password" placeholder="Password" required>
                    <button type="submit">Register</button>
                </form>
            </div>
        </div>
        <div id="layout">
            <div id="shop">
                
                <div id="products"></div>

               
            </div>
        </div>
        <div id="sidebar">
            <h2>Carrinho</h2>
            <div id="cart-items"></div>
            <div id="cart-total"></div>
        </div>
          <div id="overlay"></div>

        <script src="js/script.js"></script>
    </body>
</html>