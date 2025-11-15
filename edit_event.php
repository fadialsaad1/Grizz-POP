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

// Fetch the event
$sql = "SELECT * FROM events WHERE eventID = $eventID";
$result = $conn->query($sql);

if ($result->num_rows !== 1) {
    die("Event not found.");
}

$event = $result->fetch_assoc();

// Update event on submit
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $title = $conn->real_escape_string($_POST["title"]);
    $location = $conn->real_escape_string($_POST["location"]);
    $date = $_POST["date"];
    $time = $_POST["time"];
    $status = $conn->real_escape_string($_POST["status"]);
    $comments = $conn->real_escape_string($_POST["comments"]);

    $update = "
        UPDATE events
        SET 
            title = '$title',
            location = '$location',
            date = '$date',
            time = '$time',
            status = '$status',
            customComments = '$comments'
        WHERE eventID = $eventID
    ";

    if ($conn->query($update)) {
        echo "<script>alert('Event updated successfully!'); window.location='creation_user.php';</script>";
        exit;
    } else {
        echo "<script>alert('Error updating event: " . $conn->error . "');</script>";
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

    <label>Title:</label>
    <input type="text" name="title" value="<?= htmlspecialchars($event['title']) ?>" required>

    <label>Date:</label>
    <input type="date" name="date" value="<?= $event['date'] ?>" required>

    <label>Time:</label>
    <input type="time" name="time" value="<?= $event['time'] ?>">

    <label>Location:</label>
    <input type="text" name="location" value="<?= htmlspecialchars($event['location']) ?>" required>

    <label>Status:</label>
    <select name="status">
        <option <?= $event['status']=='Planned'?'selected':'' ?>>Planned</option>
        <option <?= $event['status']=='Scheduled'?'selected':'' ?>>Scheduled</option>
        <option <?= $event['status']=='Completed'?'selected':'' ?>>Completed</option>
    </select>

    <label>Comments:</label>
    <textarea name="comments"><?= htmlspecialchars($event['customComments'] ?? "") ?></textarea>

    <button type="submit">Save Changes</button>
</form>

</body>
</html>


<!DOCTYPE html>
<html lang="en">
<head>
    <title>Edit Event - Grizz POP!</title>
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
