<?php
$servername = "localhost";
$username = "root";     // default XAMPP username
$password = "";         // default XAMPP password is blank
$dbname = "grizz_pop_database";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Grizz POP!</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <header>
    <div>Grizz POP!</div>
    <div><button id="changeUser">Change User</button></div>
  </header>

  <main>
    <!-- Pinned Events -->
    <section class="pinned">
      <h2>Pinned Events</h2>
      <ul id="pinnedList">
        <?php foreach ($pinnedEvents as $event): ?>
          <li><?= htmlspecialchars($event) ?></li>
        <?php endforeach; ?>
      </ul>
    </section>

    <!-- Upcoming Events -->
    <section class="upcoming">
      <h2>Upcoming Events</h2>
      <ul id="upcomingList">
        <?php foreach ($upcomingEvents as $event): ?>
          <li>
            <?= htmlspecialchars($event["date"]) ?> - <?= htmlspecialchars($event["title"]) ?>
            <button class="pinButton" onclick="pinEvent('<?= addslashes($event["title"]) ?>')">📌</button>
          </li>
        <?php endforeach; ?>
      </ul>
    </section>

    <!-- Feedback Section -->
    <section class="feedback">
      <h2>Event Feedback</h2>
      <textarea id="feedback" placeholder="Write your feedback here..."></textarea>
      <button onclick="submitFeedback()">Submit Feedback</button>
    </section>
  </main>

  <script>
    // ---------------------------
    // JavaScript for interactivity
    // ---------------------------

    // Handle pinning events
    function pinEvent(eventName) {
      const pinnedList = document.getElementById("pinnedList");
      const newItem = document.createElement("li");
      newItem.textContent = eventName;
      pinnedList.appendChild(newItem);
      alert(`Event "${eventName}" pinned!`);
    }

    // Handle feedback submission
    function submitFeedback() {
      const feedback = document.getElementById("feedback").value.trim();
      if (!feedback) {
        alert("Please write some feedback before submitting.");
        return;
      }
      alert("Feedback submitted! Thank you!");
      document.getElementById("feedback").value = "";
    }

    // Change User simulation
    document.getElementById("changeUser").addEventListener("click", () => {
      const username = prompt("Enter new username:");
      if (username) {
        alert(`User changed to ${username}`);
      }
    });
  </script>
</body>
</html>
