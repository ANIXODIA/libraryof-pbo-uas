<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    die("<h1 style='color:red; text-align:center;'>403 ACCESS DENIED</h1>");
}

include 'database.php';
$db_obj = new Database();
$connection = $db_obj->conn;
$username = $_SESSION['user'];

$report_query = mysqli_query($connection, "SELECT i.username, b.title, b.grade, b.cost FROM inventory i JOIN books b ON i.book_id = b.id ORDER BY i.username ASC, b.title ASC");

$total_borrowed = mysqli_num_rows($report_query);
$total_light_held = 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Global Borrowing Report - The Library</title>
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    <style>
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #2d353f; }
        th { border-bottom: 2px solid var(--border-gold); color: var(--border-gold); }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <div>
                <h2>Master Report</h2>
                <span style="color: var(--accent-cyan);">Director Authority: Verified</span>
            </div>
            <div class="nav-links">
                <a href="update.php" style="color: var(--border-gold);">Back to Admin Panel</a>
            </div>
        </header>

        <div>
            <div style="text-align: center; margin-bottom: 30px;">
                <h1 style="color: var(--border-gold); letter-spacing: 2px; margin-bottom: 5px;">THE LIBRARY OF RUINA</h1>
                <p style="color: var(--text-muted); margin-top: 0;">Official Active Borrowing Log</p>
                <p style="font-size: 0.9rem; color: var(--text-muted);">Generated on: <?php echo date('F j, Y, g:i a'); ?></p>
            </div>

            <div style="display: flex; gap: 20px; margin-bottom: 30px; border: 1px solid var(--border-gold); padding: 20px; background: rgba(0,0,0,0.2);">
                <div style="flex: 1;">
                    <h4 style="margin: 0; color: var(--text-muted); font-size: 0.9rem;">Total Active Borrowers</h4>
                    <span style="font-size: 1.5rem; font-weight: bold; color: var(--text-light);"><?php echo $total_borrowed; ?> Volumes</span>
                </div>
            </div>

            <?php if ($total_borrowed == 0) { ?>
                <div style="text-align: center; padding: 40px; border: 1px dashed #2d353f;">
                    <p style="color: var(--text-muted);">No volumes are currently borrowed.</p>
                </div>
            <?php } else { ?>
                <table>
                    <thead>
                        <tr>
                            <th>Patron Identity</th>
                            <th>Volume Title</th>
                            <th>Danger Grade</th>
                            <th>Deposit Secured</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($report_query)) { ?>
                            <tr>
                                <td><strong style="color: var(--accent-red);"><?php echo htmlspecialchars($row['username']); ?></strong></td>
                                <td><?php echo htmlspecialchars($row['title']); ?></td>
                                <td><?php echo htmlspecialchars($row['grade']); ?></td>
                                <td><?php echo htmlspecialchars($row['cost']); ?> Light</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            <?php } ?>
            
            <div style="margin-top: 50px; text-align: right;">
                <p style="color: var(--text-muted); border-top: 1px solid #2d353f; display: inline-block; padding-top: 10px;">
                    Authorized by: Head Librarian <?php echo htmlspecialchars($username); ?>
                </p>
            </div>
        </div>
    </div>
</body>
</html>