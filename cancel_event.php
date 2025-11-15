<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "grizz_pop_database";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET["id"])) {
    $eventID = intval($_GET["id"]);

    // DELETE the event from the database
    $sql = "DELETE FROM events WHERE eventID = $eventID";

    if ($conn->query($sql)) {
        echo "<script>
                alert('Event has been cancelled and removed.');
                window.location.href = 'creation_user.php';
              </script>";
    } else {
        echo "Error: " . $conn->error;
    }
}
?>
