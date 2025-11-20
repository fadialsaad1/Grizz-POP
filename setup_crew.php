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
// FETCH Assigned EVENTS
// ---------------------------
$assignedEvents = [];
$sqlAssigned = "
  SELECT e.eventID, e.title, e.date, e.status
  FROM events e
  JOIN crewAssignment ca ON e.eventID = ca.eventID
  WHERE ca.crewID = 3
  ORDER BY e.date DESC
";

$resultAssigned = $conn->query($sqlAssigned);

if ($resultAssigned && $resultAssigned->num_rows > 0) {
  while ($row = $resultAssigned->fetch_assoc()) {
    $assignedEvents[] = $row;
  }
}

?>


<!DOCTYPE html>
<html lang="en">
  <head>
      <title>Crew User - Grizz POP!</title>
      <link rel="stylesheet" href="SetupCrew.css">
  </head>

  <body>
    <header>
  <div class="logo-container">
      <img src="images/Grizz POP.png" alt="Grizz POP Logo" class="logo">
      <span class="logo-text">Grizz POP!</span>
  </div>

  <div class="user-menu">
      <button onclick="changeUser()">Change User</button>
  </div>
</header>

    <main>
      <!-- LEFT COLUMN: ASSIGNED EVENTS -->
      <section class="assignedEvents">
        <h2>Assigned Events</h2>
        <ul>
          <?php if (count($assignedEvents) > 0): ?>
            <?php foreach ($assignedEvents as $ev): ?>
              <li>
                <?= htmlspecialchars($ev["title"]) ?>
                (<?= date("m-d-Y", strtotime($ev["date"])) ?>)
                <?= htmlspecialchars($ev["status"]) ?>
                <a class="edit-btn" href="edit_event_status.php?id=<?= $ev['eventID'] ?>">Edit Setup Status</a>

              </li>
            <?php endforeach; ?>
          <?php else: ?>
            <li>No events assigned yet.</li>
          <?php endif; ?>
        </ul>


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
