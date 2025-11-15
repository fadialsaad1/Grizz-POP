<?php
// ---------------------------
// DATABASE CONNECTION
// ---------------------------
$servername = "localhost";
$username = "root";     
$password = "";        
$dbname = "grizz_pop_database";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}



// ---------------------------
// HANDLE NEW EVENT CREATION
// ---------------------------
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["title"])) {

    $title = $conn->real_escape_string($_POST["title"]);
    $location = $conn->real_escape_string($_POST["location"]);
    $date = $_POST["date"];
    $time = $_POST["time"];
    $status = $conn->real_escape_string($_POST["status"]);
    $comments = $conn->real_escape_string($_POST["comments"]);

    // INSERT without userID column
    $insert = "
      INSERT INTO events (title, location, date, time, status, customComments)
      VALUES ('$title', '$location', '$date', '$time', '$status', '$comments')
    ";

    if ($conn->query($insert)) {
        echo "<script>alert('Event Created Successfully!');</script>";
    } else {
        echo "<script>alert('Error creating event: " . $conn->error . "');</script>";
    }
}

// ---------------------------
// FETCH EVENTS (no userID column)
// ---------------------------
$createdEvents = [];
$sqlCreated = "
  SELECT eventID, title, date, status
  FROM events
  ORDER BY date DESC
";

$resultCreated = $conn->query($sqlCreated);

if ($resultCreated && $resultCreated->num_rows > 0) {
  while ($row = $resultCreated->fetch_assoc()) {
    $createdEvents[] = $row;
  }
}

?>


<!DOCTYPE html>
<html lang="en">
  <head>
      <title>Creation User - Grizz POP!</title>
      <link rel="stylesheet" href="style.css">
  </head>

  <body>
    <header>
      <div>Grizz POP!</div>
      <div class="user-menu">
      <button onclick="changeUser()">Change User</button>
    </header>

    <main>
      <!-- LEFT COLUMN: CREATED EVENTS -->
      <section class="createdEvents">
        <h2>Created Events</h2>
        <ul>
          <?php if (count($createdEvents) > 0): ?>
            <?php foreach ($createdEvents as $ev): ?>
              <li>
                <?= htmlspecialchars($ev["title"]) ?>
                (<?= date("m-d-Y", strtotime($ev["date"])) ?>)

                <a class="edit-btn" href="edit_event.php?id=<?= $ev['eventID'] ?>">Edit</a>

                <a class="cancel-btn" 
                   href="cancel_event.php?id=<?= $ev['eventID'] ?>"
                   onclick="return confirm('Are you sure you want to cancel this event?');">
                   Cancel
                </a>
              </li>
            <?php endforeach; ?>
          <?php else: ?>
            <li>No events created yet.</li>
          <?php endif; ?>
        </ul>
      </section>

      <!-- CENTER COLUMN: CREATE NEW EVENT -->
      <section class="createEvent">
          <h2>Create New Event</h2>

          <div class="eventDetailsBox">
              <form method="POST">

                  <!-- Event Details -->
                  <div class="sectionHeader">Event Details</div>

                  <label>Title:</label>
                  <input type="text" name="title" required>

                  <label>Date:</label>
                  <input type="date" name="date" required>

                  <label>Time:</label>
                  <input type="time" name="time">

                  <label>Location:</label>
                  <input type="text" name="location" required>

                  <label>Status:</label>
                  <select name="status">
                      <option value="Planned">Planned</option>
                      <option value="Scheduled">Scheduled</option>
                      <option value="Completed">Completed</option>
                  </select>

                  <!-- Custom Comments -->
                  <div class="sectionHeader">Custom Comments</div>

                  <textarea name="comments" placeholder="Enter custom comments..."></textarea>

                  <button type="submit" class="submitBtn">Create Event</button>
              </form>
          </div>
      </section>

      <!-- RIGHT COLUMN: MESSAGING -->
      <section class="messaging">
          <h2>Messaging</h2>
          <textarea placeholder="Send a message..."></textarea>
          <button class="sendMsgBtn">Send</button>
      </section>

    </main>

    <script>
      // Change user
      function changeUser() {
      let userID = prompt("Enter your User ID:");

      if (userID === null || userID.trim() === "") {
        alert("User ID required.");
        return;
      }

      // Redirect to PHP with a GET parameter
      window.location.href = "switch_user.php?userID=" + encodeURIComponent(userID);
      }

      document.querySelector(".changeUserBtn").addEventListener("click", () => {
        const user = prompt("Enter new username:");
        if (user) alert("User changed to " + user);
      });
    </script>

  </body>
</html>
