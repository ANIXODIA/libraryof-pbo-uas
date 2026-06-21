<?php
session_start();
include 'database.php';
$db_obj = new Database();
$connection = $db_obj->conn;

if (isset($_POST['btn_register'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $check_user = mysqli_query($connection, "SELECT * FROM users WHERE username = '$username'");
    
    if (mysqli_num_rows($check_user) > 0) {
        $error_msg = "This Guest identity already exists inside the Library.";
    } else {
        if (strtolower($username) == 'angela') {
            $role = 'admin';
        } else {
            $role = 'guest';
        }
        
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $insert = mysqli_query($connection, "INSERT INTO users (username, password, role) VALUES ('$username', '$hashed_password', '$role')");
        
        if ($insert) {
            header('Location: login.php');
            exit;
        } else {
            $error_msg = "Failed to register. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Library Registration</title>
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="auth-box" style="margin-top:100px;">
        <h2>Register Identity</h2>
        <?php if(isset($error_msg)) echo "<p style='color:var(--accent-red);'>$error_msg</p>"; ?>
        
        <form method="POST" action="">
            <label>Username</label>
            <input type="text" name="username" required>
            
            <label>Password</label>
            <input type="password" name="password" required>
            
            <button type="submit" name="btn_register" class="btn">Receive Invitation</button>
        </form>
        <p style="margin-top:15px; font-size:0.9rem;"><a href="login.php" style="color:var(--text-muted);">Already registered? Login here</a></p>
    </div>
</body>
</html>