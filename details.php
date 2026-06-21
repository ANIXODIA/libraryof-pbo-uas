<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

include 'database.php';
$db_obj = new Database();
$connection = $db_obj->conn;

$username = $_SESSION['user'];
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: dashboard.php');
    exit;
}
$bookId = intval($_GET['id']);

if (isset($_POST['buy_book_id'])) {
    $duration_days = intval($_POST['duration']);
    
    $check_inv = mysqli_query($connection, "SELECT * FROM inventory WHERE username = '$username' AND book_id = '$bookId'");
    $get_book = mysqli_query($connection, "SELECT * FROM books WHERE id = '$bookId'");
    $book_data = mysqli_fetch_assoc($get_book);
    
    $get_user = mysqli_query($connection, "SELECT light FROM users WHERE username = '$username'");
    $user_data = mysqli_fetch_assoc($get_user);
    
    if (mysqli_num_rows($check_inv) > 0) {
        $msg = "You already possess this volume.";
    } elseif ($book_data['status'] == 'borrowed') {
        $msg = "Apologies, this book is currently borrowed by another patron.";
    } elseif ($user_data['light'] < $book_data['cost']) {
        $msg = "Insufficient Light. You need {$book_data['cost']} Light, but you only have {$user_data['light']}.";
    } else {
        $due_date = date('Y-m-d H:i:s', strtotime("+$duration_days days")); 
        $new_light = $user_data['light'] - $book_data['cost'];
        mysqli_query($connection, "UPDATE users SET light = '$new_light' WHERE username = '$username'");
        mysqli_query($connection, "INSERT INTO inventory (username, book_id, due_date) VALUES ('$username', '$bookId', '$due_date')");
        mysqli_query($connection, "UPDATE books SET status = 'borrowed' WHERE id = '$bookId'");
        $msg = "Successfully extracted volume for $duration_days days. Cost: {$book_data['cost']} Light.";
    }
}
$get_book = mysqli_query($connection, "SELECT * FROM books WHERE id = '$bookId'");
if (mysqli_num_rows($get_book) == 0) {
    die("<h2 style='color:red; text-align:center; padding: 50px;'>Error 404: The Library does not possess this archival record.</h2>");
}
$book = mysqli_fetch_assoc($get_book);
$get_light = mysqli_query($connection, "SELECT light FROM users WHERE username = '$username'");
$display_light = mysqli_fetch_assoc($get_light)['light'];

$check_inv = mysqli_query($connection, "SELECT * FROM inventory WHERE username = '$username' AND book_id = '$bookId'");
$already_owns = (mysqli_num_rows($check_inv) > 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($book['title']) ?> - The Library</title>
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="container">
        <header>
            <div>
                <h2>Archive Details</h2>
                <span style="color: var(--text-muted);">Current Light: <strong style="color: var(--border-gold);"><?= htmlspecialchars($display_light) ?></strong></span>
            </div>
            <div class="nav-links">
                <a href="dashboard.php">Back to Catalog</a>
            </div>
        </header>

        <?php if(isset($msg)) echo "<blockquote style='border-left:4px solid var(--accent-cyan); padding-left:10px;'>$msg</blockquote>"; ?>

        <div style="background: var(--bg-panel); border: 1px solid #2d353f; border-left: 5px solid var(--accent-cyan); padding: 40px; margin-top: 30px;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 20px;">
                <div>
                    <span class="badge" style="font-size: 1rem; padding: 8px 15px; margin-bottom: 15px; display: inline-block;">
                        <?= htmlspecialchars($book['grade']) ?>
                    </span>
                    <h1 style="margin: 0 0 10px 0; font-size: 2.5rem;"><?= htmlspecialchars($book['title']) ?></h1>
                    <p style="color: var(--text-muted); font-size: 1.1rem; margin-bottom: 20px;">
                        Originating Office / Source: <strong><?= htmlspecialchars($book['chapters']) ?></strong>
                    </p>
                </div>
                
                <div style="text-align: right; background: #1b2838; padding: 20px; border: 1px solid var(--border-gold); border-radius: 5px; min-width: 250px;">
                    <p style="margin: 0; color: var(--text-muted); text-transform: uppercase; font-size: 0.9rem;">Extraction Cost</p>
                    <h2 style="margin: 5px 0 15px 0; color: var(--border-gold); font-size: 2rem;"><?= htmlspecialchars($book['cost']) ?> Light</h2>
                    
                    <?php if ($already_owns): ?>
                        <button class="btn" style="background: #2d353f; color: var(--text-muted); cursor: not-allowed; width: 100%; border-color: #2d353f;" disabled>
                            Already Possessed
                        </button>
                    <?php elseif ($book['status'] == 'borrowed'): ?>
                        <button class="btn" style="background: #2d353f; color: #5a6b7c; cursor: not-allowed; width: 100%; border-color: #2d353f;" disabled>
                            Currently Borrowed
                        </button>
                    <?php else: ?>
                        <form method="POST" style="margin: 0;">
                            <input type="hidden" name="buy_book_id" value="<?= $book['id'] ?>">
                            
                            <label style="color: var(--text-muted); font-size: 0.9rem; display: block; text-align: left; margin-bottom: 5px;">Borrow Duration:</label>
                            <select name="duration" style="margin-bottom: 15px; width: 100%; background: #0d131a; color: white; border: 1px solid #2d353f; padding: 8px;">
                                <option value="1">1 Day (Quick Scan)</option>
                                <option value="3">3 Days (Standard Read)</option>
                                <option value="7">7 Days (Deep Study)</option>
                            </select>
                            
                            <button type="submit" class="btn" style="width: 100%; font-size: 1.1rem;">Extract Volume</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

            <div style="margin-top: 40px; border-top: 1px solid #2d353f; padding-top: 20px;">
                <h3 style="color: var(--text-light);">Archival Summary</h3>
                <p style="color: var(--text-muted); line-height: 1.6; white-space: pre-wrap;"><?php 
                        if (!empty($book['description'])) echo htmlspecialchars($book['description']);
                        else echo "No archival summary has been recorded for this volume yet.";
                ?></p>
            </div>
        </div>
    </div>
</body>
</html>
