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
$role = isset($_SESSION['role']) ? $_SESSION['role'] : 'guest';

if (isset($_POST['return_book_id'])) {
    $return_id = intval($_POST['return_book_id']);

    $check = mysqli_query($connection, "SELECT b.cost, b.title, i.due_date FROM inventory i JOIN books b ON i.book_id = b.id WHERE i.username = '$username' AND i.book_id = '$return_id'");
    
    if (mysqli_num_rows($check) == 1) {
        $book_data = mysqli_fetch_assoc($check);
        
        $current_time = date('Y-m-d H:i:s');
        $is_late = ($current_time > $book_data['due_date']);
        
        mysqli_query($connection, "DELETE FROM inventory WHERE username = '$username' AND book_id = '$return_id'");
        mysqli_query($connection, "UPDATE books SET status = 'available' WHERE id = '$return_id'");
        
        if ($is_late) {
            $msg = "<span style='color: var(--accent-red); font-weight: bold;'>LATE RETURN PENALTY!</span> You returned '{$book_data['title']}' past the deadline. Your deposit of {$book_data['cost']} Light was confiscated.";
        } else {
            mysqli_query($connection, "UPDATE users SET light = light + " . $book_data['cost'] . " WHERE username = '$username'");
            $msg = "You returned '{$book_data['title']}' on time. +{$book_data['cost']} Light restored to your balance.";
        }
    } else {
        $msg = "Error: You do not possess this volume.";
    }
}

$get_light = mysqli_query($connection, "SELECT light FROM users WHERE username = '$username'");
$display_light = mysqli_fetch_assoc($get_light)['light'];

$my_books_query = mysqli_query($connection, "SELECT b.id, b.title, b.chapters, b.cost, b.grade, b.description, i.due_date FROM books b JOIN inventory i ON b.id = i.book_id WHERE i.username = '$username'");
$total_owned = mysqli_num_rows($my_books_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Account - The Library</title>
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="container">
        <header>
            <div>
                <h2>Account details</h2>
                <span style="color: var(--text-muted);">Patron: <strong><?= htmlspecialchars($username) ?></strong> (<?= strtoupper($role) ?>)</span>
            </div>
            <div class="nav-links">
                <a href="dashboard.php" style="color: var(--border-gold);">Back to Catalog</a>
                <a href="login.php" style="color: var(--accent-red);">Logout</a>
            </div>
        </header>

        <?php if(isset($msg)) echo "<blockquote style='border-left:4px solid var(--border-gold); padding-left:10px;'>$msg</blockquote>"; ?>

        <div style="display: flex; gap: 20px; margin-top: 20px; margin-bottom: 30px; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 200px; background: var(--bg-panel); border: 1px solid #2d353f; border-left: 4px solid var(--border-gold); padding: 15px;">
                <h4 style="margin: 0; color: var(--text-muted); font-size: 0.9rem; text-transform: uppercase;">Current Balance</h4>
                <span style="font-size: 1.8rem; font-weight: bold; color: var(--text-light);"><?= htmlspecialchars($display_light) ?> Light</span>
            </div>
            <div style="flex: 1; min-width: 200px; background: var(--bg-panel); border: 1px solid #2d353f; border-left: 4px solid var(--accent-cyan); padding: 15px;">
                <h4 style="margin: 0; color: var(--text-muted); font-size: 0.9rem; text-transform: uppercase;">Total Borrowed</h4>
                <span style="font-size: 1.8rem; font-weight: bold; color: var(--text-light);"><?= $total_owned ?> Volumes</span>
            </div>
        </div>

        <h3>Currently Borrowed Volumes</h3>
        
        <?php if ($total_owned == 0): ?>
            <div style="background: var(--bg-panel); border: 1px solid #2d353f; padding: 30px; text-align: center; color: var(--text-muted);">
                <p>You currently have no borrowed books. Return to the Catalog to borrow volumes.</p>
                <a href="dashboard.php" class="btn" style="margin-top: 15px; display: inline-block; text-decoration: none;">Browse Catalog</a>
            </div>
        <?php else: ?>
            <div class="grid">
                <?php while ($book = mysqli_fetch_assoc($my_books_query)): ?>
                    <div class="card" style="border-left: 4px solid var(--accent-cyan);">
                        <span class="badge" style="background: #1b2838; border-color: var(--accent-cyan);"><?= htmlspecialchars($book['grade']) ?></span>
                        <h3><?= htmlspecialchars($book['title']) ?></h3>
                        
                        <?php 
                            // Time Calculation Logic
                            $now = time();
                            $due = strtotime($book['due_date']);
                            if ($now > $due) {
                                echo "<p style='color: var(--accent-red); font-weight: bold; margin: 5px 0;'>STATUS: OVERDUE (Light Forfeited)</p>";
                            } else {
                                $days_left = ceil(($due - $now) / 86400);
                                echo "<p style='color: var(--accent-cyan); margin: 5px 0;'>Time Left: $days_left Days</p>";
                            }
                        ?>
                        
                        <p style="color: var(--text-muted); font-size: 0.9rem;">Source: <?= htmlspecialchars($book['chapters']) ?></p>
                        <p style="color: var(--border-gold);">Deposit Return: <?= htmlspecialchars($book['cost']) ?> Light</p>
                        
                        <button type="button" class="btn" onclick="document.getElementById('content-<?= $book['id'] ?>').style.display = (document.getElementById('content-<?= $book['id'] ?>').style.display === 'none') ? 'block' : 'none';" style="width: 100%; margin-top: 10px; padding: 5px; font-size: 0.9rem; border-color: #2d353f; color: var(--text-light);">Read Volume</button>
                        
                        <div id="content-<?= $book['id'] ?>" style="display: none; margin-top: 10px; padding: 15px; background: #0d0f12; border: 1px solid #2d353f; font-size: 0.9rem; color: var(--text-muted); max-height: 150px; overflow-y: auto;">
                            <?= nl2br(htmlspecialchars($book['description'])) ?>
                        </div>
                        
                        <form method="POST" style="margin-top: 15px;" onsubmit="return confirm('Return this book to the Library?');">
                            <input type="hidden" name="return_book_id" value="<?= $book['id'] ?>">
                            <button type="submit" class="btn btn-danger" style="width: 100%;">Return Volume</button>
                        </form>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>