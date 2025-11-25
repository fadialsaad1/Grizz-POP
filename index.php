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
// HANDLE RSVP SUBMISSION
// ---------------------------
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submitRSVP"])) {
    $eventID   = intval($_POST["rsvp_eventID"]);
    $firstname = $conn->real_escape_string($_POST["firstname"]);
    $lastname  = $conn->real_escape_string($_POST["lastname"]);
    $email     = $conn->real_escape_string($_POST["email"]);
    $phone     = $conn->real_escape_string($_POST["phone"]);
    $role      = $conn->real_escape_string($_POST["role"]);
    $response  = $conn->real_escape_string($_POST["response"]); // Yes / No

    // 1) Check if user already exists by email
    $userID = null;
$checkUser = $conn->query("SELECT userID FROM users WHERE email = '$email' LIMIT 1");

if ($checkUser && $checkUser->num_rows > 0) {
    // user exists → get ID
    $userID = $checkUser->fetch_assoc()['userID'];

    // 2) Check if this user already RSVP'd for the SAME event
    $checkDuplicate = $conn->query("
        SELECT rsvpID FROM rsvp 
        WHERE userID = $userID AND eventID = $eventID 
        LIMIT 1
    ");

    if ($checkDuplicate && $checkDuplicate->num_rows > 0) {
        echo "<script>alert('You already RSVP’d for this event.'); window.history.back();</script>";
        exit;
    }
    } else {
        // 2) Insert new user
        $insertUser = "
            INSERT INTO users (firstname, lastname, email, phone, role)
            VALUES ('$firstname', '$lastname', '$email', '$phone', '$role')
        ";
        if ($conn->query($insertUser)) {
            $userID = $conn->insert_id;
        }
    }

    if ($userID !== null) {
        // 3) Insert or update RSVP
        $checkRSVP = $conn->query("
            SELECT rsvpID
            FROM rsvp
            WHERE userID = $userID AND eventID = $eventID
            LIMIT 1
        ");

        if ($checkRSVP && $checkRSVP->num_rows > 0) {
            // Update existing
            $rowRSVP = $checkRSVP->fetch_assoc();
            $rsvpID = intval($rowRSVP["rsvpID"]);
            $conn->query("
                UPDATE rsvp
                SET response = '$response'
                WHERE rsvpID = $rsvpID
            ");
        } else {
            // Insert new
            $conn->query("
                INSERT INTO rsvp (response, eventID, userID)
                VALUES ('$response', $eventID, $userID)
            ");
        }

        echo "<script>alert('RSVP submitted successfully!');</script>";
    } else {
        echo "<script>alert('Error saving user/RSVP.');</script>";
    }
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
  $rating   = intval($_POST["rating"]);
  $eventID  = intval($_POST["eventID"]);

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
                 data-event-id="<?= $event['eventID'] ?>"
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

              <!-- PIN + RSVP ON THE RIGHT (inline) -->
              <div style="display:flex; gap:8px; align-items:center;">
                <button class="pinButton"
                  onclick="event.stopPropagation(); pinEvent(<?= $event['eventID'] ?>, '<?= addslashes($event["title"]) ?>')">
                  📌
                </button>

                <button class="rsvpBtn"
                  onclick="event.stopPropagation(); openRSVPModal(<?= $event['eventID'] ?>, '<?= htmlspecialchars(addslashes($event["title"])) ?>')">
                  RSVP
                </button>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </section>

      <!-- RIGHT COLUMN: FEEDBACK -->
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

        <img id="modalImage" src="" alt="Event Image" style="width:100%; border-radius:10px; margin-bottom:15px; display:none;">

        <p><strong>Description:</strong><br><span id="modalDescription"></span></p>
        <p><strong>Location:</strong> <span id="modalLocation"></span></p>
        <p><strong>Date:</strong> <span id="modalDate"></span></p>
        <p><strong>Time:</strong> <span id="modalTime"></span></p>
      </div>
    </div>

    <!-- RSVP MODAL (FORM + YES/NO INSIDE) -->
<div id="rsvpModal" class="modal">
  <div class="modal-content">
    <span class="close" onclick="closeRSVPModal()">&times;</span>

    <h2>RSVP for Event</h2>
    <p id="rsvpEventTitle" class="event-title"></p>

    <form method="POST">
      <input type="hidden" name="rsvp_eventID" id="rsvp_eventID">

      <label>First Name:</label>
      <input type="text" name="firstname" required>

      <label>Last Name:</label>
      <input type="text" name="lastname" required>

      <label>Email:</label>
      <input type="email" name="email" required>

      <label>Phone:</label>
      <input type="text" name="phone" required>

      <label>Role:</label>
      <select name="role" required>
        <option value="">Select role</option>
        <option value="Attendee">Attendee</option>
        <option value="Organizer">Organizer</option>
        <option value="Crew">Crew</option>
      </select>

      <label style="margin-top: 10px;">Will you reserve a spot?</label>
      <div class="rsvp-toggle">
        <input type="radio" id="rsvpYes" name="response" value="Yes" required>
        <label for="rsvpYes" class="toggleOption">Yes</label>

        <input type="radio" id="rsvpNo" name="response" value="No">
        <label for="rsvpNo" class="toggleOption">No</label>
      </div>

      <button type="submit" name="submitRSVP" class="submitBtn">Submit RSVP</button>
    </form>
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
        window.location.href = "switch_user.php?userID=" + encodeURIComponent(userID);
      }

      // Pin actions
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

      // Star rating
      const stars = document.querySelectorAll(".star-rating span");
      const ratingInput = document.getElementById("ratingValue");

      stars.forEach(star => {
        star.addEventListener("click", () => {
          const value = Number(star.getAttribute("data-value"));
          ratingInput.value = value;

          stars.forEach(s => s.classList.remove("selected"));
          for (let i = 0; i < value; i++) {
            stars[i].classList.add("selected");
          }
        });
      });

      // Event modal
      function openEventModal(card) {
        document.getElementById("modalTitle").textContent      = card.dataset.title;
        document.getElementById("modalDescription").textContent= card.dataset.description;
        document.getElementById("modalLocation").textContent   = card.dataset.location;
        document.getElementById("modalDate").textContent       = card.dataset.date;
        document.getElementById("modalTime").textContent       = card.dataset.time;

        document.getElementById("eventModal").style.display = "block";
      }

      function closeEventModal() {
        document.getElementById("eventModal").style.display = "none";
      }

      // RSVP modal
      function openRSVPModal(eventID, title) {
        document.getElementById("rsvp_eventID").value   = eventID;
        document.getElementById("rsvpEventTitle").textContent = title;

        // Clear previous selection
        const radios = document.querySelectorAll("#rsvpModal input[name='response']");
        radios.forEach(r => r.checked = false);

        document.getElementById("rsvpModal").style.display = "block";
      }

      function closeRSVPModal() {
        document.getElementById("rsvpModal").style.display = "none";
      }

      // Close modals when clicking outside
      window.onclick = function(e) {
        const eventModal = document.getElementById("eventModal");
        const rsvpModal  = document.getElementById("rsvpModal");

        if (e.target === eventModal) {
            eventModal.style.display = "none";
        }
        if (e.target === rsvpModal) {
            rsvpModal.style.display = "none";
        }
      };
    </script>
  </body>
</html>
