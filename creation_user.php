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
    $status = "Waiting for setup crew";
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
// ---------------------------
// MESSAGING SYSTEM
// ---------------------------
$messages = [];
$currentUserID = 3; // Current user ID - adjust as needed
$currentUserName = "You"; // Default
$currentUserRole = "Crew"; // Default

// Get current user's name from users table
$userQuery = $conn->query("SELECT firstname, lastname, role FROM users WHERE userID = $currentUserID");
if ($userQuery && $userQuery->num_rows > 0) {
    $userData = $userQuery->fetch_assoc();
    $currentUserName = $userData['firstname'] . " " . $userData['lastname'];
    $currentUserRole = $userData['role'];
}

// Handle sending new messages
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['message'])) {
    $message = $conn->real_escape_string($_POST['message']);
    $senderID = $currentUserID;
    $senderType = strtolower($currentUserRole); // Convert role to senderType
    
    if (!empty(trim($message))) {
        $insertMessage = "INSERT INTO messages (senderID, senderType, message, timestamp) 
                         VALUES ('$senderID', '$senderType', '$message', NOW())";
        
        if ($conn->query($insertMessage)) {
            // Message sent successfully - redirect to same page to avoid form resubmission
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        } else {
            echo "<script>alert('Error sending message: " . $conn->error . "');</script>";
        }
    } else {
        echo "<script>alert('Please enter a message');</script>";
    }
}

// Fetch recent messages with actual user names and roles
$sqlMessages = "
    SELECT m.*, 
           u.firstname, 
           u.lastname, 
           u.role as userRole,
           CASE 
               WHEN m.senderID = $currentUserID THEN 'You'
               ELSE CONCAT(u.firstname, ' ', u.lastname)
           END as displayName
    FROM messages m
    JOIN users u ON m.senderID = u.userID
    ORDER BY m.timestamp DESC 
    LIMIT 50
";

$resultMessages = $conn->query($sqlMessages);
if ($resultMessages && $resultMessages->num_rows > 0) {
    while ($row = $resultMessages->fetch_assoc()) {
        $messages[] = $row;
    }
    $messages = array_reverse($messages); // Show oldest first
}
?>


<!DOCTYPE html>
<html lang="en">
  <head>
      <title>Creation User - Grizz POP!</title>
      <link rel="stylesheet" href="creationUser.css">
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
                  <select name="status" disabled>
                      <option value="Waiting For Setup Crew">Waiting For Setup Crew</option>
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
          
          <!-- Messages Display Area -->
          <div class="messages-container">
              <?php if (count($messages) > 0): ?>
                  <?php foreach ($messages as $msg): ?>
                      <div class="message <?= $msg['senderID'] == $currentUserID ? 'message-sent' : 'message-received' ?>">
                          <div class="message-bubble">
                              <div class="message-content">
                                  <?= htmlspecialchars($msg['message'] ?? '') ?>
                              </div>
                              <div class="message-info">
                                  <div class="message-user">
                                      <span class="sender-name"><?= htmlspecialchars($msg['displayName'] ?? 'Unknown User') ?></span>
                                      <span class="user-badge"><?= ucfirst($msg['userRole'] ?? 'Unknown') ?></span>
                                  </div>
                                  <span class="message-time"><?= date("g:i A", strtotime($msg['timestamp'])) ?></span>
                              </div>
                          </div>
                      </div>
                  <?php endforeach; ?>
              <?php else: ?>
                  <div class="no-messages">No messages yet. Start the conversation!</div>
              <?php endif; ?>
          </div>
          
          <!-- Message Input Form -->
          <form method="POST" class="message-form">
              <textarea name="message" placeholder="Type your message here..." required></textarea>
              <button type="submit" class="sendMsgBtn">Send Message</button>
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

      document.querySelector(".changeUserBtn").addEventListener("click", () => {
        const user = prompt("Enter new username:");
        if (user) alert("User changed to " + user);
      });
    </script>

  </body>
</html>
