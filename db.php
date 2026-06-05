<?php
// ============================================================
// db.php — Σύνδεση με βάση δεδομένων
// ============================================================
$host     = 'localhost';
$dbname   = 'car_dealership';
$username = 'root';
$password = '';          // Κενό στο XAMPP by default

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("<div style='font-family:sans-serif;padding:20px;color:red;'>
         <h2>⚠️ Σφάλμα σύνδεσης βάσης δεδομένων</h2>
         <p>" . $conn->connect_error . "</p>
         <p>Βεβαιωθείτε ότι: 1) Το MySQL είναι ενεργό στο XAMPP &nbsp; 2) Έχετε εισάγει το αρχείο <b>car_dealership.sql</b> στο phpMyAdmin</p>
         </div>");
}

$conn->set_charset("utf8");
?>
