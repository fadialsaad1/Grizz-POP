<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "grizz_pop_database";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Make sure an ID was passed
if (!isset($_GET['id'])) {
    die("No event ID provided.");
}

$eventID = intval($_GET['id']);

// Fetch the event data from both events and crewAssignment tables
$sql = "SELECT e.*, ca.setupStatus, ca.issueReport 
        FROM events e 
        LEFT JOIN crewAssignment ca ON e.eventID = ca.eventID 
        WHERE e.eventID = $eventID";
$result = $conn->query($sql);

if ($result->num_rows !== 1) {
    die("Event not found.");
}

$event = $result->fetch_assoc();

// Update event on submit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
     if (isset($_POST['complete'])) {
        $status = 'Completed';
        $issueReport = $conn->real_escape_string($_POST["issueReport"] ?? '');
    } else {
        $status = $conn->real_escape_string($_POST["status"]);
        $issueReport = $conn->real_escape_string($_POST["issueReport"] ?? '');
    }
    // Update events table
    $updateEvents = "UPDATE events SET status = '$status' WHERE eventID = $eventID";
    
    // Update crewAssignment table - fixed syntax error (removed extra comma)
    $updateCrew = "UPDATE crewAssignment SET setupStatus = '$status', issueReport = '$issueReport' WHERE eventID = $eventID";

       // Start transaction for multiple updates
    $conn->begin_transaction();
    
    try {
        // Execute both updates
        if ($conn->query($updateEvents) && $conn->query($updateCrew)) {
            $conn->commit();
            echo "<script>alert('Event Status updated successfully!'); window.location='setup_crew.php';</script>";
            exit;
        } else {
            throw new Exception($conn->error);
        }
    } catch (Exception $e) {
        $conn->rollback();
        echo "<script>alert('Error updating event status: " . $conn->error . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Event</title>
</head>
<body>

<h2>Edit Event</h2>

<form method="POST">

    <div class="form-group">
        <label for="status">Event Status:</label>
        <select name="status" id="status">
        <option value="Planned" <?= ($event['status'] ?? '') == 'Planned' ? 'selected' : '' ?>>Planned</option>
        <option value="Scheduled" <?= ($event['status'] ?? '') == 'Scheduled' ? 'selected' : '' ?>>Scheduled</option>
        <option value="Completed" <?= ($event['status'] ?? '') == 'Completed' ? 'selected' : '' ?>>Completed</option>
        </select>
    </div>
    
   <div class="sectionHeader">Issue Report</div>
    <div class="form-group">
        <label for="issueReport">Issue Description:</label>
        <textarea name="issueReport" id="issueReport" placeholder="Describe any issues encountered during setup or execution..."><?= htmlspecialchars($event['issueReport'] ?? '') ?></textarea>    </div>
    

    <button type="submit">Save Changes</button>
</form>

</body>
</html>


<!DOCTYPE html>
<html lang="en">
<head>
    <title>Edit Event Status - Grizz POP!</title>
    <link rel="stylesheet" href="style.css">

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f7f7f7;
            margin: 0;
            padding: 0;
        }

        header {
            background: #333;
            padding: 15px;
            color: white;
            font-size: 20px;
        }

        .edit-container {
            margin: 40px auto;
            width: 60%;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0,0,0,0.15);
        }

        h2 {
            text-align: center;
            margin-bottom: 25px;
            font-size: 28px;
        }

        form label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
        }

        input, select, textarea {
            width: 100%;
            padding: 12px;
            font-size: 16px;
            border: 1px solid #999;
            border-radius: 6px;
            margin-top: 5px;
        }

        textarea {
            height: 120px;
            resize: vertical;
        }

        .sectionHeader {
            margin-top: 35px;
            text-align: center;
            font-weight: bold;
            padding-bottom: 5px;
            border-bottom: 2px solid #111;
            margin-bottom: 15px;
            font-size: 18px;
        }

        .button-row {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 30px;
        }

        .saveBtn {
            padding: 12px 25px;
            background: #222;
            border: none;
            color: white;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
        }

        .saveBtn:hover {
            background: #444;
        }

        .cancelBtn {
            padding: 12px 25px;
            background: #aaa;
            border: none;
            color: white;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
        }

        .cancelBtn:hover {
            background: #888;
        }
    </style>
</head>
