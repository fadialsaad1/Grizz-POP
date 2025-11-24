<?php
  session_start();

  // For now: default to user 1 if session not set (use switch_user.php to change)
  if (!isset($_SESSION['userID'])) {
      $_SESSION['userID'] = 1;
  }
  $currentUser = intval($_SESSION['userID']);

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
  // FETCH PINNED EVENTS FOR CURRENT USER
  // ---------------------------
  $pinnedEvents = [];
  $sqlPinned = "
    SELECT e.eventID, e.title
    FROM pinned_events p
    JOIN events e ON p.eventID = e.eventID
    WHERE p.userID = ?
    ORDER BY e.date ASC
  ";

  $stmtPinned = $conn->prepare($sqlPinned);
  $stmtPinned->bind_param("i", $currentUser);
  $stmtPinned->execute();
  $resPinned = $stmtPinned->get_result();
  if ($resPinned) {
      while ($row = $resPinned->fetch_assoc()) {
          $pinnedEvents[] = $row;
      }
  }
  $stmtPinned->close();


  // ---------------------------
  // FETCH UPCOMING EVENTS
  // ---------------------------
  $upcomingEvents = [];
  $sql = "
    SELECT eventID, title, description, date, time, location, status
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
        <ul id="pinnedList">
          <?php foreach ($pinnedEvents as $p): ?>
            <li data-id="<?= $p['eventID'] ?>">
              <?= htmlspecialchars($p['title']) ?>
              <button class="unpin-btn" onclick="unpinEvent(<?= $p['eventID'] ?>)">❌</button>
            </li>
          <?php endforeach; ?>
        </ul>

      </section>

      <!-- CENTER COLUMN: UPCOMING -->
      <section class="upcoming">
        <h2>Upcoming Events</h2>
        <div id="upcomingList">
            <?php foreach ($upcomingEvents as $event): ?>
              <div class="event-card"
                  onclick="openEventModal(this)"
                  data-title="<?= htmlspecialchars($event['title']) ?>"
                  data-description="<?= htmlspecialchars($event['description'] ?? 'No description provided.') ?>"
                  data-location="<?= htmlspecialchars($event['location'] ?? 'TBA') ?>"
                  data-date="<?= date('m-d-Y', strtotime($event['date'])) ?>"
                  data-time="<?= htmlspecialchars(date('h:i A', strtotime($event['time']))) ?>"
              >
                  <div>
                      <strong><?= date("m-d-Y", strtotime($event["date"])) ?></strong><br>
                      <?= htmlspecialchars($event["title"]) ?><br>
                      <small style="opacity:0.7;"><?= htmlspecialchars($event["status"]) ?></small>
                  </div>

                  <button class="pinButton"
                    onclick="event.stopPropagation(); pinEvent(<?= $event['eventID'] ?>, '<?= addslashes($event["title"]) ?>')">📌
                  </button>
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

    <!-- EVENT DETAILS MODAL -->
    <div id="eventModal" class="modal">
      <div class="modal-content">
        <span class="close" onclick="closeEventModal()">&times;</span>

        <h2 id="modalTitle"></h2>

        <p><strong>Description:</strong><br><span id="modalDescription"></span></p>
        <p><strong>Location:</strong> <span id="modalLocation"></span></p>
        <p><strong>Date:</strong> <span id="modalDate"></span></p>
        <p><strong>Time:</strong> <span id="modalTime"></span></p>
      </div>
    </div>


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
      function pinEvent(eventID, title) {
        fetch("event_pin_action.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `eventID=${eventID}&action=pin`
        })
        .then(res => res.text())
        .then(data => {
            if (data === "OK") {
                const pinnedList = document.getElementById("pinnedList");

                // Create <li> if not already pinned
                if (!document.querySelector(`#pinnedList li[data-id="${eventID}"]`)) {
                    const li = document.createElement("li");
                    li.dataset.id = eventID;
                    li.innerHTML = `
                        ${title}
                        <button class="unpin-btn" onclick="unpinEvent(${eventID})">❌</button>
                    `;
                    pinnedList.appendChild(li);
                }
            } else {
                console.log("Pin failed:", data);
            }
        });
      }

      function unpinEvent(eventID) {
          fetch("event_pin_action.php", {
              method: "POST",
              headers: { "Content-Type": "application/x-www-form-urlencoded" },
              body: `eventID=${eventID}&action=unpin`
          })
          .then(res => res.text())
          .then(data => {
              if (data === "OK") {
                  const li = document.querySelector(`#pinnedList li[data-id='${eventID}']`);
                  if (li) li.remove();
              } else {
                  console.log("Unpin failed:", data);
              }
          });
      }
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

    <script>
    function openEventModal(card) {
      document.getElementById("modalTitle").textContent = card.dataset.title;
      document.getElementById("modalDescription").textContent = card.dataset.description;
      document.getElementById("modalLocation").textContent = card.dataset.location;
      document.getElementById("modalDate").textContent = card.dataset.date;
      document.getElementById("modalTime").textContent = card.dataset.time;

      document.getElementById("eventModal").style.display = "block";
    }

    function closeEventModal() {
      document.getElementById("eventModal").style.display = "none";
    }

    // Close when clicking outside the modal
    window.onclick = function(e) {
      const modal = document.getElementById("eventModal");
      if (e.target === modal) modal.style.display = "none";
    };
    </script>
  </body>
</html>
