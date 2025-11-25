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
    $status = "Waiting for setup crew";
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
        /* ======================================================================
   GLOBAL — Premium Modern UI
====================================================================== */
        body {
            margin: 0;
            font-family: "Inter", sans-serif;
            background: linear-gradient(135deg, #dfe9f3, #ffffff);
            color: #222;
            animation: fadeIn 0.6s ease-in-out;
        }

        /* Fade In Animation */
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

        /* ======================================================================
   HEADER — Glass + Glow
====================================================================== */
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

        /* ======================================================================
   EDIT CARD — Glassmorphism Panel
====================================================================== */
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

        /* Title */
        .edit-title {
            font-size: 2.2rem;
            font-weight: 800;
            text-align: center;
            letter-spacing: 1px;
            color: #222;
            margin-bottom: 30px;
        }

        /* ======================================================================
   FORM INPUTS
====================================================================== */
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

        /* ======================================================================
   BUTTONS — Gradient + Glow
====================================================================== */
        .button-row {
            margin-top: 40px;
            display: flex;
            justify-content: center;
            gap: 22px;
        }

        /* Save Button */
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

        /* Cancel Button */
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

               <label>Status:</label>
                  <select name="status" disabled>
                      <option value="Waiting For Setup Crew">Waiting For Setup Crew</option>
                  </select>
         

        <label>Setup List:</label>
        <input type="text" name="setup_list" required>

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
