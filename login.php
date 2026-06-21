<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'database.php';
$db_obj = new Database();
$connection = $db_obj->conn;

if (isset($_POST['btn_login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $query = mysqli_query($connection, "SELECT * FROM users WHERE username = '$username'");
    
    if (mysqli_num_rows($query) > 0) {
        $data = mysqli_fetch_assoc($query);
        
        if (password_verify($password, $data['password'])) {
            $_SESSION['user'] = $data['username'];
            $_SESSION['role'] = $data['role']; 
            header('Location: dashboard.php');
            exit;
        } else {
            $error_msg = "Invalid Credentials. Wrong password.";
        }
    } else {
        $error_msg = "Invalid Credentials. Username not found.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Library Authentication</title>
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="auth-box" style="margin-top:100px;">
        <h2>Sign In</h2>
        <?php if(isset($error_msg)) { echo "<p style='color:var(--accent-red);'>$error_msg</p>"; } ?>
        
        <form method="POST" action="">
            <label>Username</label>
            <input type="text" name="username" required>
            
            <label>Password</label>
            <input type="password" name="password" required>
            
            <button type="submit" name="btn_login" class="btn">Enter The Library</button>
        </form>
        <p style="margin-top:15px; font-size:0.9rem;"><a href="register.php" style="color:var(--text-muted);">New Guest? Register profile</a></p>
    </div>
</body>
</html>