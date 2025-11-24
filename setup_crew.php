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
  SELECT e.eventID, e.title, e.date, e.status, e.location, e.setupList
  FROM events e
  JOIN crewAssignment ca ON e.eventID = ca.eventID
  WHERE ca.crewID = 3 AND (e.status != 'Completed' AND ca.setupStatus != 'Completed')
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
      <style>
        .event-item { margin-bottom: 12px; }
        .event-title { font-weight: 600; margin-bottom: 6px; }
        .event-details .field { display: inline-block; margin-right: 18px; vertical-align: middle; }
        .event-details label { font-weight: 600; margin-right: 6px; }
      </style>
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
      

         <!-- CENTER COLUMN: CREATE NEW EVENT -->
      <section class="assignedEvents">
          <h2>Assigned Event Details</h2>
          <ul>
                  <!-- Event Details -->
              <?php if (count($assignedEvents) > 0): ?>   
                <?php foreach ($assignedEvents as $ev): ?>
                  <li class="event-item">
                    <div class="event-title"><?= htmlspecialchars($ev["title"]) ?></div>
                    <div class="event-details">
                      <div class="field"><label>Date:</label><span>(<?= date("m-d-Y", strtotime($ev["date"])) ?>)</span></div>
                      <div class="field"><label>Location:</label><span><?= htmlspecialchars($ev["location"]) ?></span></div>
                      <div class="field"><label>Status:</label><span><?= htmlspecialchars($ev["status"]) ?></span></div>
                      <div class="field"><label>Setup List:</label><span><?= htmlspecialchars($ev["setupList"]) ?></span></div>
                    </div>
                     <!-- Complete Button -->
                    <?php if ($ev["status"] != 'Completed'): ?>
                      <form method="POST" action="complete_event_setup.php" style="display: inline;">
                        <input type="hidden" name="eventID" value="<?= $ev['eventID'] ?>">
                        <button type="submit" class="complete-btn" onclick="return confirm('Mark <?= htmlspecialchars($ev['title']) ?> as completed?')">
                          Mark Complete
                        </button>
                      </form>
                    <?php else: ?>
                    <?php endif; ?>
                  </li>
                <?php endforeach; ?>
                <?php else: ?>
            <li>No events assigned yet.</li>
              <?php endif; ?>
          </ul>

          <section class="assignedEvents">
        <h2>Edit Event Status</h2>
        <ul>
          <?php if (count($assignedEvents) > 0): ?>
            <?php foreach ($assignedEvents as $ev): ?>
              <?= htmlspecialchars($ev["title"]) ?>
              <li>
                
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
