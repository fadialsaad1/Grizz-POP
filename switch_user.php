<?php
// ---------------------------
// DATABASE CONNECTION
// ---------------------------
$servername = "localhost";
$username = "root";     // default for XAMPP
$password = "";         // default is blank
$dbname = "grizz_pop_database"; 

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Make sure userID was passed
if (!isset($_GET["userID"])) {
    die("No User ID provided.");
}

$userID = intval($_GET["userID"]);

// Prepare query
$sql = "SELECT role FROM users WHERE userID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userID);
$stmt->execute(); 

// Required so fetch() works properly
$stmt->store_result();

if ($stmt->num_rows === 0) {
    die("User ID not found.");
}

$stmt->bind_result($role);
$stmt->fetch();

// Redirect based on role
if ($role === "Organizer") {
    header("Location: creation_user.php");
    exit;
} elseif ($role === "Crew") {
    header("Location: setup_crew.php");
    exit;
} elseif ($role === "Attendee") {
    header("Location: index.php");
    exit;
} else {
    echo "Role not recognized.";
}

$stmt->close();
$conn->close();
?>
