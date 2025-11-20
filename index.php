<?php
// ---------------------------
// DATABASE CONNECTION
// ---------------------------
$servername = "localhost";
$username = "root";     // default for XAMPP
$password = "";         // default is blank
$dbname = "grizz_pop_database"; 

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// ---------------------------
// FETCH UPCOMING EVENTS
// ---------------------------
$upcomingEvents = [];
$sql = "
  SELECT eventID, title, date, time, location, status
  FROM events
  WHERE status IN ('Planned', 'Scheduled')
  ORDER BY date ASC
";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $upcomingEvents[] = $row;
  }
}

// ---------------------------
// FETCH COMPLETED EVENTS (for feedback dropdown)
// ---------------------------
$completedEvents = [];
$sqlCompleted = "
  SELECT eventID, title
  FROM events
  WHERE status = 'Completed'
  ORDER BY date DESC
";
$resultCompleted = $conn->query($sqlCompleted);

if ($resultCompleted && $resultCompleted->num_rows > 0) {
  while ($row = $resultCompleted->fetch_assoc()) {
    $completedEvents[] = $row;
  }
}

// ---------------------------
// HANDLE FEEDBACK SUBMISSION
// ---------------------------
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["feedbackText"])) {
  $feedback = $conn->real_escape_string($_POST["feedbackText"]);
  $rating = intval($_POST["rating"]);
  $eventID = intval($_POST["eventID"]);

  // For simplicity, assume userID = 1
  $userID = 1;

  $insert = "INSERT INTO feedback (rating, comment, eventID, userID)
             VALUES ('$rating', '$feedback', '$eventID', '$userID')";

  if ($conn->query($insert)) {
    echo "<script>alert('Feedback submitted successfully!');</script>";
  } else {
    echo "<script>alert('Error saving feedback.');</script>";
  }
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <title>Grizz POP!</title>
    <link rel="stylesheet" href="index.css">
  </head>
  <body>
    <header>
            <div class="logo-container">
        <img src="images/Grizz POP.png" alt="Grizz POP Logo" class="logo">
        <span class="logo-text">Grizz POP!</span>
            </div>
        <div class="user-menu"></div>
        <button onclick="changeUser()">Change User</button>
    </header>

    <main>
      <!-- LEFT COLUMN: PINNED -->
      <section class="pinned">
        <h2>Pinned Events</h2>
        <ul id="pinnedList"></ul>
      </section>

      <!-- CENTER COLUMN: UPCOMING -->
      <section class="upcoming">
        <h2>Upcoming Events</h2>
        <div id="upcomingList">
            <?php foreach ($upcomingEvents as $event): ?>
            <div class="event-card">
                <div>
                    <strong><?= date("m-d-Y", strtotime($event["date"])) ?></strong><br>
                            <?= htmlspecialchars($event["title"]) ?><br>
                       <small style="opacity:0.7;"><?= htmlspecialchars($event["status"]) ?></small>
                </div>
                <button class="pinButton" onclick="pinEvent('<?= addslashes($event["title"]) ?>')">📌</button>
        </div>
         <?php endforeach; ?>
        </div>

      </section>

      <!-- RIGHT COMLUMN: FEEDBACK -->
      <section class="feedback">
        <h2>Event Feedback</h2>
        <form method="POST" id="feedbackForm">
          <select name="eventID" required>
            <option value="">Select Completed Event</option>
            <?php foreach ($completedEvents as $event): ?>
              <option value="<?= $event['eventID'] ?>"><?= htmlspecialchars($event['title']) ?></option>
            <?php endforeach; ?>
          </select>
          <br><br>

          <label>Rating:</label>

<div class="star-rating">
  <span data-value="1">★</span>
  <span data-value="2">★</span>
  <span data-value="3">★</span>
  <span data-value="4">★</span>
  <span data-value="5">★</span>
</div>

<input type="hidden" name="rating" id="ratingValue" required>

          <textarea name="feedbackText" placeholder="Write your feedback here..." required></textarea><br>
          <button type="submit">Submit Feedback</button>
        </form>
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

      // Pinning simulation
      function pinEvent(eventName) {
        const pinnedList = document.getElementById("pinnedList");
        const newItem = document.createElement("li");
        newItem.textContent = eventName;
        pinnedList.appendChild(newItem);
      }

      // Change User prompt
      document.getElementById("changeUser").addEventListener("click", () => {
        const username = prompt("Enter new username:");
        if (username) alert(`User changed to ${username}`);
      });
    </script>

      <script>
  const stars = document.querySelectorAll(".star-rating span");
  const ratingInput = document.getElementById("ratingValue");

  stars.forEach((star, index) => {
    star.addEventListener("click", () => {
      const value = Number(star.getAttribute("data-value"));
      ratingInput.value = value;

      // Remove all selections
      stars.forEach(s => s.classList.remove("selected"));

      // Fill stars from LEFT to RIGHT up to selected value
      for (let i = 0; i < value; i++) {
        stars[i].classList.add("selected");
      }
    });
  });
</script>
  </body>
</html>
