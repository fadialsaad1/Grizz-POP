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

$sql = "SELECT * FROM events WHERE eventID = $eventID";
$result = $conn->query($sql);

if ($result->num_rows !== 1) {
    die("Event not found.");
}

$event = $result->fetch_assoc();

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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Event - Grizz POP!</title>

    <style>
        /* GLOBAL */
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f1f1f1;
            color: #222;
        }

        /* HEADER */
        header {
            background: #111;
            padding: 15px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.25);
        }

        .logo-container {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo {
            width: 45px;
        }

        .logo-text {
            font-size: 24px;
            font-weight: bold;
            letter-spacing: 1px;
        }

        /* MAIN WRAPPER */
        .edit-card {
            max-width: 900px;
            margin: 45px auto;
            background: white;
            border-radius: 14px;
            padding: 40px;
            box-shadow: 0 5px 18px rgba(0,0,0,0.18);
        }

        .edit-title {
            font-size: 32px;
            font-weight: bold;
            text-align: center;
            color: #111;
            margin-bottom: 25px;
        }

        /* FORM */
        label {
            display: block;
            margin-top: 22px;
            font-size: 16px;
            font-weight: bold;
        }

        input, textarea, select {
            width: 100%;
            margin-top: 6px;
            padding: 14px;
            font-size: 16px;
            background: #fafafa;
            border: 2px solid #ccc;
            border-radius: 10px;
            transition: 0.25s;
        }

        input:focus, textarea:focus, select:focus {
            border-color: #111;
            outline: none;
            background: #fff;
        }

        textarea {
            min-height: 140px;
            resize: vertical;
        }

        /* BUTTONS */
        .button-row {
            margin-top: 35px;
            display: flex;
            justify-content: center;
            gap: 20px;
        }

        .saveBtn {
            background: #111;
            color: white;
            padding: 14px 32px;
            font-size: 17px;
            border-radius: 10px;
            border: none;
            cursor: pointer;
            font-weight: bold;
            letter-spacing: 0.5px;
            transition: 0.25s;
        }

        .saveBtn:hover {
            background: #333;
        }

        .cancelBtn {
            background: #777;
            color: white;
            padding: 14px 32px;
            font-size: 17px;
            border-radius: 10px;
            border: none;
            cursor: pointer;
            letter-spacing: 0.5px;
            transition: 0.25s;
        }

        .cancelBtn:hover {
            background: #555;
        }

    </style>
</head>

<body>

<header>
    <div class="logo-container">
        <img src="images/Grizz POP.png" class="logo" alt="Grizz POP Logo">
        <span class="logo-text">Grizz POP!</span>
    </div>
</header>

<div class="edit-card">
    <div class="edit-title">Edit Event</div>

    <form method="POST">

        <label>Title:</label>
        <input type="text" name="title" 
               value="<?= htmlspecialchars($event['title']) ?>" required>

        <label>Date:</label>
        <input type="date" name="date" value="<?= $event['date'] ?>" required>

        <label>Time:</label>
        <input type="time" name="time" value="<?= $event['time'] ?>">

        <label>Location:</label>
        <input type="text" name="location" 
               value="<?= htmlspecialchars($event['location']) ?>" required>

        <label>Comments:</label>
        <textarea name="comments"><?= htmlspecialchars($event['customComments'] ?? "") ?></textarea>

        <div class="button-row">
            <button type="submit" class="saveBtn">Save Changes</button>
            <a href="creation_user.php">
                <button type="button" class="cancelBtn">Cancel</button>
            </a>
        </div>

    </form>
</div>

</body>
</html>
