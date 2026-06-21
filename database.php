<?php
class Database {
    public $host = "sql110.infinityfree.com";
    public $user = "if0_42219881";
    public $pass = "Ajhuman9";
    public $db = "if0_42219881_anixia";
    public $conn;

    public function __construct() {
        $this->conn = mysqli_connect($this->host, $this->user, $this->pass, $this->db);
        if (!$this->conn) {
            die("Database connection failed: " . mysqli_connect_error());
        }
    }
}
class UserProfile {
    protected $username;
    public function __construct($uname) {
        $this->username = $uname;
    }
}
class Admin extends UserProfile {
    public function showRole() {
        return "Head Librarian: <strong style='color: var(--accent-red);'>" . htmlspecialchars($this->username) . "</strong> (DIRECTOR)";
    }
}
class Guest extends UserProfile {
    public function showRole() {
        return "Patron: <strong>" . htmlspecialchars($this->username) . "</strong> (GUEST)";
    }
}
?>