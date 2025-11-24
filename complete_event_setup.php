<?php
// complete_event.php
$servername = "localhost";
$username = "root";     
$password = "";        
$dbname = "grizz_pop_database";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['eventID'])) {
    $eventID = intval($_POST['eventID']);
    
    // Update events table
    $updateEvents = "UPDATE events SET status = 'Completed' WHERE eventID = $eventID";
    
    // Update crewAssignment table
    $updateCrew = "UPDATE crewAssignment SET setupStatus = 'Completed' WHERE eventID = $eventID";
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        if ($conn->query($updateEvents) && $conn->query($updateCrew)) {
            $conn->commit();
            echo "<script>alert('Event marked as completed!'); window.location='setup_crew.php';</script>";
        } else {
            throw new Exception($conn->error);
        }
    } catch (Exception $e) {
        $conn->rollback();
        echo "<script>alert('Error completing event: " . $conn->error . "'); window.location='setup_crew.php';</script>";
    }
} else {
    header("Location: setup_crew.php");
}
?>