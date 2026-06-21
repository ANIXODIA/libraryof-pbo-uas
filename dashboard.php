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

if ($_SESSION['role'] === 'admin') {
    $active_user = new Admin($username);
} else {
    $active_user = new Guest($username);
}
if (isset($_POST['quick_borrow_id'])) {
    $book_id = intval($_POST['quick_borrow_id']);
    
    $get_book = mysqli_query($connection, "SELECT * FROM books WHERE id = '$book_id'");
    $book_data = mysqli_fetch_assoc($get_book);
    
    $get_user = mysqli_query($connection, "SELECT light FROM users WHERE username = '$username'");
    $user_data = mysqli_fetch_assoc($get_user);
    
    if (!$book_data) {
        $msg = "Error: This volume does not exist in the archives.";
    } elseif ($book_data['status'] === 'borrowed') {
        $msg = "Apologies, '{$book_data['title']}' is currently borrowed by another patron.";
    } elseif ($user_data['light'] < $book_data['cost']) {
        $msg = "Insufficient Light for deposit. You need {$book_data['cost']} Light.";
    } else {
        $due_date = date('Y-m-d H:i:s', strtotime("+3 days"));
        
        $remaining_light = $user_data['light'] - $book_data['cost'];
        
        mysqli_query($connection, "UPDATE users SET light = '$remaining_light' WHERE username = '$username'");
        mysqli_query($connection, "INSERT INTO inventory (username, book_id, due_date) VALUES ('$username', '$book_id', '$due_date')");
        mysqli_query($connection, "UPDATE books SET status = 'borrowed' WHERE id = '$book_id'");
        
        $msg = "Successfully quick-borrowed '{$book_data['title']}' for 3 Days. Deposit: {$book_data['cost']} Light.";
    }
}

if (isset($_POST['gather_light'])) {
    mysqli_query($connection, "UPDATE users SET light = light + 100 WHERE username = '$username'");
    $msg = "You gathered ambient Light. +100 Light added to your balance.";
}
$ui_light = mysqli_fetch_assoc(mysqli_query($connection, "SELECT light FROM users WHERE username = '$username'"))['light'];
$stat_total_books = mysqli_fetch_assoc(mysqli_query($connection, "SELECT COUNT(*) as count FROM books"))['count'];
$stat_total_transactions = mysqli_fetch_assoc(mysqli_query($connection, "SELECT COUNT(*) as count FROM inventory"))['count'];
$stat_user_books = mysqli_fetch_assoc(mysqli_query($connection, "SELECT COUNT(*) as count FROM inventory WHERE username = '$username'"))['count'];

$catalog = mysqli_query($connection, "SELECT * FROM books ORDER BY id ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Library Dashboard</title>
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="container">
        <header>
            <div>
                <h2>The Library</h2>
                <span style="color: var(--text-muted);">Welcome back, <?= $active_user->showRole(); ?></span>
                <br>
                <div style="display: flex; align-items: center; gap: 15px; margin-top: 10px;">
                    <span style="color: var(--border-gold); font-weight: bold; font-size: 1.1rem;">Current Light: <?= htmlspecialchars($ui_light) ?></span>
                    <form method="POST" style="margin: 0;">
                        <input type="hidden" name="gather_light" value="1">
                        <button type="submit" style="background: transparent; border: 1px solid var(--accent-cyan); color: var(--accent-cyan); padding: 5px 10px; cursor: pointer; text-transform: uppercase; font-size: 0.8rem; transition: 0.3s;">+ Gather Light</button>
                    </form>
                </div>
            </div>
            <div class="nav-links">
                <a href="account.php" style="color: var(--accent-cyan);">My Account</a>
                <a href="dashboard.php">Catalog</a>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <a href="update.php" style="color: var(--border-gold); font-weight: bold;">[Admin Panel]</a>
                <?php endif; ?>
                <a href="index.php" style="color: var(--accent-red);">Logout</a>
            </div>
        </header>

        <?php if(isset($msg)) echo "<blockquote style='border-left:4px solid var(--accent-cyan); padding-left:10px;'>$msg</blockquote>"; ?>

        <div style="display: flex; gap: 20px; margin-top: 20px; margin-bottom: 30px; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 200px; background: var(--bg-panel); border: 1px solid #2d353f; border-left: 4px solid var(--border-gold); padding: 15px;">
                <h4 style="margin: 0; color: var(--text-muted); font-size: 0.9rem; text-transform: uppercase;">Total Archives</h4>
                <span style="font-size: 1.8rem; font-weight: bold; color: var(--text-light);"><?= $stat_total_books ?> Volumes</span>
            </div>
            <div style="flex: 1; min-width: 200px; background: var(--bg-panel); border: 1px solid #2d353f; border-left: 4px solid var(--accent-cyan); padding: 15px;">
                <h4 style="margin: 0; color: var(--text-muted); font-size: 0.9rem; text-transform: uppercase;">Global Borrowers</h4>
                <span style="font-size: 1.8rem; font-weight: bold; color: var(--text-light);"><?= $stat_total_transactions ?> Active</span>
            </div>
            <div style="flex: 1; min-width: 200px; background: var(--bg-panel); border: 1px solid #2d353f; border-left: 4px solid var(--accent-red); padding: 15px;">
                <h4 style="margin: 0; color: var(--text-muted); font-size: 0.9rem; text-transform: uppercase;">Your Borrowed Books</h4>
                <span style="font-size: 1.8rem; font-weight: bold; color: var(--text-light);"><?= $stat_user_books ?> Books</span>
            </div>
        </div>

        <div class="search-bar">
            <input type="text" id="searchBox" placeholder="Search volumes by Title or Danger Grade...">
        </div>

        <h3>Available Archives</h3>
        <div class="grid" id="booksGrid">
            <?php while ($book = mysqli_fetch_assoc($catalog)): ?>
                <div class="card" data-title="<?= htmlspecialchars(strtolower($book['title'])) ?>" data-grade="<?= htmlspecialchars(strtolower($book['grade'])) ?>">
                    <span class="badge"><?= htmlspecialchars($book['grade']) ?></span>
                    <h3><?= htmlspecialchars($book['title']) ?></h3>
                    <p style="color: var(--text-muted); font-size: 0.9rem;">Source: <?= htmlspecialchars($book['chapters']) ?></p>
                    <p style="color: var(--border-gold);">Deposit: <?= htmlspecialchars($book['cost']) ?> Light</p>
                    
                    <div style="display: flex; gap: 10px; margin-top: 15px;">
                        <a href="details.php?id=<?= $book['id'] ?>" class="btn" style="flex: 1; text-align: center; text-decoration: none; background: transparent; color: var(--accent-cyan); border-color: var(--accent-cyan);">Inspect</a>
                        
                        <?php if ($book['status'] === 'available'): ?>
                            <form method="POST" style="margin: 0; flex: 1;">
                                <input type="hidden" name="quick_borrow_id" value="<?= $book['id'] ?>">
                                <button type="submit" class="btn" style="width: 100%;" title="Quick Borrow for 3 Days">Borrow</button>
                            </form>
                        <?php else: ?>
                            <button class="btn" style="flex: 1; width: 100%; background: #2d353f; color: #5a6b7c; cursor: not-allowed; border-color: #2d353f;" disabled>Borrowed</button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const searchBox = document.getElementById('searchBox');
        const cards = document.querySelectorAll('.card');
        searchBox.addEventListener('input', (event) => {
            const searchTerm = event.target.value.toLowerCase();
            cards.forEach(card => {
                const title = card.dataset.title;
                const grade = card.dataset.grade;
                card.style.display = (title.includes(searchTerm) || grade.includes(searchTerm)) ? 'block' : 'none';
            });
        });
    });
    </script>
</body>
</html>