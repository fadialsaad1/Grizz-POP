<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "grizz_pop_database";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_GET['id'])) {
    die("No event ID provided.");
}

$eventID = intval($_GET['id']);

$sql = "SELECT e.*, ca.setupStatus, ca.issueReport 
        FROM events e 
        LEFT JOIN crewAssignment ca ON e.eventID = ca.eventID 
        WHERE e.eventID = $eventID";
$result = $conn->query($sql);

if ($result->num_rows !== 1) {
    die("Event not found.");
}

$event = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
     if (isset($_POST['complete'])) {
        $status = 'Completed';
        $issueReport = $conn->real_escape_string($_POST["issueReport"] ?? '');
    } else {
        $status = $conn->real_escape_string($_POST["status"]);
        $issueReport = $conn->real_escape_string($_POST["issueReport"] ?? '');
    }
    $updateEvents = "UPDATE events SET status = '$status' WHERE eventID = $eventID";
    
    $updateCrew = "UPDATE crewAssignment SET setupStatus = '$status', issueReport = '$issueReport' WHERE eventID = $eventID";

    $conn->begin_transaction();
    
    try {
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
    <header>
    <div class="logo-container">
        <img src="images/Grizz POP.png" class="logo" alt="Grizz POP Logo">
        <span class="logo-text">Grizz POP!</span>
    </div>
</header>

<div class="edit-card">
    <div class="edit-title">Edit Event Status</div>

<form method="POST">

    <div class="form-group">
        <label for="status">Event Status:</label>
        <select name="status" id="status">
        <option value="Planned" <?= ($event['status'] ?? '') == 'Planned' ? 'selected' : '' ?>>Planned</option>
        <option value="Scheduled" <?= ($event['status'] ?? '') == 'Scheduled' ? 'selected' : '' ?>>Scheduled</option>
        <option value="Completed" <?= ($event['status'] ?? '') == 'Completed' ? 'selected' : '' ?>>Completed</option>
        </select>
    </div>
    
    <div class="form-group">
        <label for="issueReport">Issue Report:</label>
        <textarea name="issueReport" id="issueReport" placeholder="Describe any issues encountered during setup or execution..."><?= htmlspecialchars($event['issueReport'] ?? '') ?></textarea>    </div>
    

     <div class="button-row">
            <button type="submit" class="saveBtn">Save Changes</button>
            <a href="setup_crew.php">
                <button type="button" class="cancelBtn">Cancel</button>
            </a>
        </div>
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
            margin: 0;
            font-family: "Inter", sans-serif;
            background: linear-gradient(135deg, #dfe9f3, #ffffff);
            color: #222;
            animation: fadeIn 0.6s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(5px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        header {
            background: rgba(34, 40, 49, 0.85);
            backdrop-filter: blur(14px);
            padding: 18px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: white;
            border-bottom: 1px solid rgba(255,255,255,0.12);
            box-shadow: 0 5px 20px rgba(0,0,0,0.35);
        }

        .logo-container {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo {
            width: 55px;
            filter: drop-shadow(0 3px 5px rgba(0,0,0,0.3));
        }

        .logo-text {
            font-size: 1.9rem;
            font-weight: 800;
            letter-spacing: 1.3px;
        }

        .edit-card {
            max-width: 900px;
            margin: 55px auto;
            padding: 45px;
            border-radius: 20px;
            background: rgba(255,255,255,0.75);
            backdrop-filter: blur(14px);
            border: 1px solid rgba(255,255,255,0.4);
            box-shadow: 0 14px 32px rgba(0,0,0,0.18);
            animation: fadeIn 0.6s ease-in-out;
            transition: 0.3s ease;
        }

        .edit-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 18px 40px rgba(0,0,0,0.22);
        }

        .edit-title {
            font-size: 2.2rem;
            font-weight: 800;
            text-align: center;
            letter-spacing: 1px;
            color: #222;
            margin-bottom: 30px;
        }

        label {
            display: block;
            margin-top: 20px;
            font-size: 15px;
            font-weight: 700;
            color: #222;
            letter-spacing: 0.5px;
        }

        input, textarea, select {
            width: 100%;
            margin-top: 8px;
            padding: 14px;
            font-size: 15px;
            background: rgba(255,255,255,0.85);
            border: 2px solid #d7dce5;
            border-radius: 12px;
            transition: 0.25s ease;
        }

        input:focus,
        textarea:focus,
        select:focus {
            border-color: #00adb5;
            box-shadow: 0 0 8px rgba(0,173,181,0.35);
            background: #fff;
            outline: none;
        }

        textarea {
            min-height: 150px;
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
            margin-top: 40px;
            display: flex;
            justify-content: center;
            gap: 22px;
        }

        .saveBtn {
            background: linear-gradient(135deg, #00c9d2, #009198);
            color: white;
            padding: 14px 34px;
            font-size: 17px;
            border-radius: 12px;
            border: none;
            cursor: pointer;
            font-weight: 700;
            letter-spacing: 0.5px;
            transition: 0.3s ease;
            box-shadow: 0 4px 14px rgba(0,173,181,0.35);
        }

        .saveBtn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 26px rgba(0,173,181,0.45);
        }

        .cancelBtn {
            background: linear-gradient(135deg, #393e46, #2e3238);
            color: white;
            padding: 14px 34px;
            font-size: 17px;
            border-radius: 12px;
            border: none;
            cursor: pointer;
            font-weight: 700;
            letter-spacing: 0.5px;
            transition: 0.3s ease;
            box-shadow: 0 4px 12px rgba(57,62,70,0.25);
        }

        .cancelBtn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 24px rgba(57,62,70,0.35);
        }
    </style>
</head>
