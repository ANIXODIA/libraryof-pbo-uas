<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    die("<h1 style='color:red; text-align:center; margin-top:50px;'>403 ACCESS DENIED</h1>");
}

include 'database.php';
$db_obj = new Database();
$connection = $db_obj->conn;

$username = $_SESSION['user'];

if (isset($_POST['delete_book_id'])) {
    $del_id = mysqli_real_escape_string($connection, $_POST['delete_book_id']);
    mysqli_query($connection, "DELETE FROM inventory WHERE book_id = '$del_id'");
    mysqli_query($connection, "DELETE FROM books WHERE id = '$del_id'");
    $status = "Book incinerated.";
}

if (isset($_POST['save_book'])) {
    $title = mysqli_real_escape_string($connection, $_POST['title']);
    $chapters = mysqli_real_escape_string($connection, $_POST['chapters']);
    $cost = mysqli_real_escape_string($connection, $_POST['cost']);
    $grade = mysqli_real_escape_string($connection, $_POST['grade']);
    $description = mysqli_real_escape_string($connection, $_POST['description']);
    $edit_id = isset($_POST['edit_id']) ? mysqli_real_escape_string($connection, $_POST['edit_id']) : 0;

    if ($edit_id > 0) {
        mysqli_query($connection, "UPDATE books SET title='$title', chapters='$chapters', cost='$cost', grade='$grade', description='$description' WHERE id='$edit_id'");
        $status = "Book updated.";
    } else {
        mysqli_query($connection, "INSERT INTO books (title, chapters, cost, grade, description) VALUES ('$title', '$chapters', '$cost', '$grade', '$description')");
        $status = "New book added.";
    }
}

if (isset($_POST['delete_username'])) {
    $target_user = mysqli_real_escape_string($connection, $_POST['delete_username']);
    if ($target_user == $_SESSION['user']) {
        $status = "You cannot delete yourself!";
    } else {
        mysqli_query($connection, "DELETE FROM inventory WHERE username = '$target_user'");
        mysqli_query($connection, "DELETE FROM users WHERE username = '$target_user'");
        $status = "Patron deleted.";
    }
}

if (isset($_POST['save_user'])) {
    $target_user = mysqli_real_escape_string($connection, $_POST['target_username']);
    $new_light = mysqli_real_escape_string($connection, $_POST['light']);
    $new_role = mysqli_real_escape_string($connection, $_POST['role']);
    mysqli_query($connection, "UPDATE users SET light='$new_light', role='$new_role' WHERE username='$target_user'");
    $status = "Patron updated.";

$edit_mode = false;
$edit_data = ['id' => '', 'title' => '', 'chapters' => '', 'grade' => 'Canard', 'cost' => '', 'description' => ''];
if (isset($_GET['edit'])) {
    $edit_mode = true;
    $edit_id = mysqli_real_escape_string($connection, $_GET['edit']);
    $get_book = mysqli_query($connection, "SELECT * FROM books WHERE id = '$edit_id'");
    $edit_data = mysqli_fetch_assoc($get_book);
}

$edit_user_mode = false;
$edit_user_data = [];
if (isset($_GET['edit_user'])) {
    $edit_user_mode = true;
    $target = mysqli_real_escape_string($connection, $_GET['edit_user']);
    $get_user = mysqli_query($connection, "SELECT username, light, role FROM users WHERE username = '$target'");
    $edit_user_data = mysqli_fetch_assoc($get_user);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Director's Panel - The Library</title>
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    <style>
        input, select, textarea { width: 100%; padding: 10px; margin-bottom: 15px; background: #1b2838; border: 1px solid #2d353f; color: var(--text-light); }
        textarea { min-height: 100px; }
        .section-box { background: var(--bg-panel); border: 1px solid var(--border-gold); padding: 30px; margin-bottom: 40px; }
        th, td { padding: 15px 10px; }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <div>
                <h2>Director Room</h2>
                <span style="color: var(--accent-cyan);">Welcome, Head Librarian <?php echo htmlspecialchars($username); ?></span>
            </div>
            <div class="nav-links">
                <a href="report.php" style="color: var(--accent-cyan); font-weight: bold;">[View Report]</a>
                <a href="dashboard.php">Back to Catalog</a>
                <a href="login.php" style="color: var(--accent-red);">Logout</a>
            </div>
        </header>

        <?php if(isset($status)) echo "<blockquote style='border-left:4px solid var(--border-gold); padding-left:10px;'>$status</blockquote>"; ?>

        <h2 style="border-bottom: 2px solid var(--accent-red); padding-bottom: 10px; color: var(--accent-red);">Patron Management</h2>
        
        <?php if ($edit_user_mode) { ?>
        <div class="section-box" style="border-color: var(--accent-red);">
            <h3 style="color: var(--accent-red);">Edit Patron: <?php echo htmlspecialchars($edit_user_data['username']); ?></h3>
            <form method="POST" action="update.php">
                <input type="hidden" name="target_username" value="<?php echo htmlspecialchars($edit_user_data['username']); ?>">
                <label>Light Balance</label>
                <input type="number" name="light" value="<?php echo htmlspecialchars($edit_user_data['light']); ?>" required>
                <label>Role</label>
                <select name="role">
                    <option value="guest" <?php if($edit_user_data['role'] == 'guest') echo 'selected'; ?>>Guest</option>
                    <option value="admin" <?php if($edit_user_data['role'] == 'admin') echo 'selected'; ?>>Admin</option>
                </select>
                <button type="submit" name="save_user" class="btn" style="background: var(--accent-red); border-color: var(--accent-red);">Save User</button>
                <a href="update.php" class="btn">Cancel</a>
            </form>
        </div>
        <?php } ?>

        <div style="background: var(--bg-panel); padding: 20px; overflow-x: auto; margin-bottom: 50px;">
            <table style='width: 100%; text-align: left; border-collapse: collapse;'>
                <tr style='border-bottom: 1px solid var(--accent-red); color: var(--accent-red);'>
                    <th>Username</th><th>Light</th><th>Role</th><th>Actions</th>
                </tr>
                <?php 
                $users_q = mysqli_query($connection, "SELECT * FROM users ORDER BY role ASC, username ASC");
                while ($u = mysqli_fetch_assoc($users_q)) { 
                ?>
                    <tr style='border-bottom: 1px solid #2d353f;'>
                        <td><strong><?php echo htmlspecialchars($u['username']); ?></strong></td>
                        <td><?php echo htmlspecialchars($u['light']); ?></td>
                        <td><?php echo strtoupper(htmlspecialchars($u['role'])); ?></td>
                        <td style='display: flex; gap: 10px;'>
                            <a href='update.php?edit_user=<?php echo urlencode($u['username']); ?>' class='btn' style='padding: 5px 10px; font-size: 0.8rem; text-decoration: none; background: transparent; color: var(--accent-red); border-color: var(--accent-red);'>Edit</a>
                            <form method='POST' style='margin:0;' onsubmit="return confirm('Erase patron?');">
                                <input type='hidden' name='delete_username' value='<?php echo htmlspecialchars($u['username']); ?>'>
                                <button type='submit' class='btn btn-danger' style='padding: 5px 10px; font-size: 0.8rem;'>Erase</button>
                            </form>
                        </td>
                    </tr>
                <?php } ?>
            </table>
        </div>

        <h2 style="border-bottom: 2px solid var(--border-gold); padding-bottom: 10px; color: var(--border-gold);">Archive Management</h2>
        
        <div class="section-box">
            <h3 style="color: var(--border-gold);"><?php echo $edit_mode ? "Edit Book" : "Add New Book"; ?></h3>
            <form method="POST" action="update.php">
                <?php if ($edit_mode) { ?>
                    <input type="hidden" name="edit_id" value="<?php echo htmlspecialchars($edit_data['id']); ?>">
                <?php } ?>
                
                <label>Book Title</label>
                <input type="text" name="title" value="<?php echo htmlspecialchars($edit_data['title']); ?>" required>
                
                <label>Source</label>
                <input type="text" name="chapters" value="<?php echo htmlspecialchars($edit_data['chapters']); ?>" required>
                
                <label>Danger Grade</label>
                <select name="grade">
                    <?php 
                    $grades = ["Canard", "Urban Myth", "Urban Legend", "Urban Plague", "Urban Nightmare", "Star of the City", "Impurity"];
                    foreach ($grades as $g) {
                        $sel = ($edit_data['grade'] == $g) ? "selected" : "";
                        echo "<option value='$g' $sel>$g</option>";
                    }
                    ?>
                </select>
                
                <label>Deposit Cost</label>
                <input type="number" name="cost" value="<?php echo htmlspecialchars($edit_data['cost']); ?>" required>
                
                <label>Description</label>
                <textarea name="description"><?php echo htmlspecialchars($edit_data['description']); ?></textarea>
                
                <div style="display: flex; gap: 15px;">
                    <button type="submit" name="save_book" class="btn" style="flex: 1;"><?php echo $edit_mode ? "Save Book" : "Add Book"; ?></button>
                    <?php if ($edit_mode) { ?>
                        <a href="update.php" class="btn" style="background: transparent; border-color: var(--text-muted); color: var(--text-muted);">Cancel</a>
                    <?php } ?>
                </div>
            </form>
        </div>

        <div style="background: var(--bg-panel); padding: 20px; overflow-x: auto;">
            <table style='width: 100%; text-align: left; border-collapse: collapse;'>
                <tr style='border-bottom: 1px solid var(--border-gold); color: var(--border-gold);'>
                    <th>Title</th><th>Grade</th><th>Deposit</th><th>Status</th><th>Actions</th>
                </tr>
                <?php 
                $books_q = mysqli_query($connection, "SELECT * FROM books ORDER BY id DESC");
                while ($b = mysqli_fetch_assoc($books_q)) { 
                    $status_color = ($b['status'] == 'available') ? "var(--accent-cyan)" : "#5a6b7c";
                ?>
                    <tr style='border-bottom: 1px solid #2d353f;'>
                        <td><?php echo htmlspecialchars($b['title']); ?></td>
                        <td><?php echo htmlspecialchars($b['grade']); ?></td>
                        <td><?php echo htmlspecialchars($b['cost']); ?></td>
                        <td style='color: <?php echo $status_color; ?>;'><?php echo htmlspecialchars($b['status']); ?></td>
                        <td style='display: flex; gap: 10px;'>
                            <a href='update.php?edit=<?php echo htmlspecialchars($b['id']); ?>' class='btn' style='padding: 5px 10px; font-size: 0.8rem; text-decoration: none; background: transparent; color: var(--accent-cyan); border-color: var(--accent-cyan);'>Edit</a>
                            <form method='POST' style='margin:0;' onsubmit="return confirm('Incinerate book?');">
                                <input type='hidden' name='delete_book_id' value='<?php echo htmlspecialchars($b['id']); ?>'>
                                <button type='submit' class='btn btn-danger' style='padding: 5px 10px; font-size: 0.8rem;'>Incinerate</button>
                            </form>
                        </td>
                    </tr>
                <?php } ?>
            </table>
        </div>
    </div>
</body>
</html>