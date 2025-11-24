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
  SELECT e.eventID, e.title, e.date, e.status, e.location, e.setupList, ca.issueReport
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
                <div class="field"><label>Issue Report:</label><span> <?= htmlspecialchars($ev["issueReport"]) ?></span></div>
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
