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
    die("No event selected.");
}

$eventID = intval($_GET['id']);

// ---------------------------
// GET EVENT TITLE
// ---------------------------
$eventQuery = $conn->query("SELECT title FROM events WHERE eventID = $eventID");
$eventData = $eventQuery->fetch_assoc();
$eventTitle = $eventData["title"];

// ---------------------------
// GET RSVP LIST (JOIN USERS + RSVP TABLE)
// ---------------------------
$rsvps = [];
$rsvpQuery = $conn->query("
    SELECT 
        u.userID,
        u.firstname, 
        u.lastname, 
        u.email, 
        u.phone, 
        u.role,
        r.response
    FROM rsvp r
    JOIN users u ON r.userID = u.userID
    WHERE r.eventID = $eventID
");

if ($rsvpQuery && $rsvpQuery->num_rows > 0) {
    while ($row = $rsvpQuery->fetch_assoc()) {
        $rsvps[] = $row;
    }
}

// ---------------------------
// EXPORT TO CSV
// ---------------------------
if (isset($_GET['export']) && $_GET['export'] == "csv") {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=reservations.csv');

    $output = fopen("php://output", "w");
    fputcsv($output, ["First Name", "Last Name", "Email", "Phone", "Role", "Response"]);

    foreach ($rsvps as $r) {
        fputcsv($output, [
            $r["firstname"],
            $r["lastname"],
            $r["email"],
            $r["phone"],
            $r["role"],
            $r["response"]
        ]);
    }
    fclose($output);
    exit;
}

// COUNTS
$total = count($rsvps);
$totalYes = count(array_filter($rsvps, fn($x) => $x["response"] == "Yes"));
$totalNo = count(array_filter($rsvps, fn($x) => $x["response"] == "No"));
?>
<!DOCTYPE html>
<html>
<head>
    <title>Reservations for <?= htmlspecialchars($eventTitle) ?></title>
    <style>
body {
    font-family: Arial, sans-serif;
    background: linear-gradient(to bottom right, #e9f3f8, #f7fafc);
    padding: 30px;      /* REMOVE TOP PADDING */
    padding-top: 0;     /* <-- FIX */
    margin: 0;
}


/* Page Title */
h1 {
    font-size: 28px;
    font-weight: bold;
    margin-bottom: 5px;
    color: #333;
}

/* Subtitle */
.sub-title {
    color: #666;
    margin-bottom: 25px;
    font-size: 15px;
}

/* Summary Counts Box */
.counts {
    background: #fff;
    padding: 16px 22px;
    border-radius: 16px;
    display: inline-block;
    box-shadow: 0 8px 20px rgba(0,0,0,0.08);
    font-size: 15px;
    margin-bottom: 25px;
}

/* Controls Line */
.top-controls {
    display: flex;
    gap: 12px;
    margin-bottom: 25px;
}

input, select {
    padding: 10px 14px;
    border-radius: 12px;
    border: 1px solid #d6d6d6;
    background: #fff;
    box-shadow: 0 2px 6px rgba(0,0,0,0.05);
    transition: 0.2s;
}

input:focus, select:focus {
    border-color: #00a7b7;
    outline: none;
    box-shadow: 0 0 6px rgba(0,167,183,0.4);
}

/* Export Button */
.export-btn {
    background: #00A7B7;
    color: white;
    padding: 10px 20px;
    border-radius: 14px;
    box-shadow: 0 4px 10px rgba(0,167,183,0.3);
    text-decoration: none;
    transition: 0.2s;
}

.export-btn:hover {
    transform: scale(1.05);
}

/* RSVP Block Container */
.rsvp-block {
    background: #fff;
    padding: 18px 20px;
    margin-bottom: 18px;
    border-radius: 18px;
    cursor: pointer;
    box-shadow: 0 8px 22px rgba(0,0,0,0.08);
    transition: 0.2s;
    border-left: 6px solid #00A7B7;
}

.rsvp-block:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 26px rgba(0,0,0,0.12);
}

/* First Name Label */
.rsvp-block strong {
    font-size: 18px;
    color: #333;
}

/* Detail Panel */
.details {
    margin-top: 12px;
    display: none;
    padding: 12px 15px;
    border-radius: 14px;
    background: #f9f9f9;
    box-shadow: inset 0 0 8px rgba(0,0,0,0.05);
    animation: fadeIn 0.2s ease-in-out;
}

/* Animation */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-3px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Details Text */
.details p {
    margin: 5px 0;
    color: #444;
}

/* ===== HEADER ===== */
header {
    position: fixed;          /* stays at the top */
    top: 0;
    left: 0;
    width: 100%;
    z-index: 999;

    background: rgba(34, 40, 49, 0.85);
    backdrop-filter: blur(14px);

    padding: 18px 40px;
    display: flex;
    justify-content: flex-start; /* aligns logo + text to the left */
    align-items: center;
    gap: 15px;

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


/* ===== TITLE SECTION ===== */
.page-title-box {
    margin: 110px auto 25px auto; /* adds space below the title */
    background: white;
    padding: 30px 40px;
    width: 96%;
    border-radius: 18px;

    display: flex;
    align-items: center;
    gap: 20px;

    box-shadow: 0 6px 18px rgba(0,0,0,0.08);
}

.title-icon {
    font-size: 40px;
    opacity: 0.8;
}

.page-title {
    font-size: 34px;
    font-weight: 700;
    margin: 0;
}

.page-subtitle {
    margin-top: 4px;
    opacity: 0.7;
    font-size: 15px;
}

    </style>
</head>

<body>

<header>
    <div class="logo-container">
        <img src="images/Grizz POP.png" class="logo">
        <span class="logo-text">Grizz POP!</span>
    </div>
</header>

<div class="page-title-box">
    <div class="title-icon">📄</div>
    <div>
        <h1 class="page-title">Reservations — <?= htmlspecialchars($eventTitle) ?></h1>
        <p class="page-subtitle">View, filter, and export RSVPs</p>
    </div>
</div>

<!-- COUNTS -->
<div class="counts">
    <b>Total:</b> <?= $total ?> |
    <b>Yes:</b> <?= $totalYes ?> |
    <b>No:</b> <?= $totalNo ?>
</div>

<!-- SEARCH + FILTERS + EXPORT -->
<div class="top-controls">
    <input type="text" id="searchBox" placeholder="Search name...">

    <select id="filterResponse">
        <option value="">Filter: All</option>
        <option value="Yes">Reserved (Yes)</option>
        <option value="No">Not Reserved (No)</option>
    </select>

    <select id="filterRole">
        <option value="">Role: All</option>
        <option value="Attendee">Attendee</option>
        <option value="Crew">Crew</option>
        <option value="Organizer">Organizer</option>
    </select>

    <a class="export-btn" href="?id=<?= $eventID ?>&export=csv">Export CSV</a>
</div>

<!-- RSVP LIST -->
<?php if ($total > 0): ?>
    <?php foreach ($rsvps as $r): ?>
        <div class="rsvp-block"
             data-name="<?= strtolower($r['firstname'] . ' ' . $r['lastname']) ?>"
             data-response="<?= $r['response'] ?>"
             data-role="<?= $r['role'] ?>"
             onclick="toggleDetails(this)">

            <strong><?= htmlspecialchars($r['firstname'] . " " . $r['lastname']) ?></strong>

            <div class="details">
                <p><b>Last Name:</b> <?= htmlspecialchars($r['lastname']) ?></p>
                <p><b>Email:</b> <?= htmlspecialchars($r['email']) ?></p>
                <p><b>Phone:</b> <?= htmlspecialchars($r['phone']) ?></p>
                <p><b>Role:</b> <?= htmlspecialchars($r['role']) ?></p>
                <p><b>Response:</b> <?= htmlspecialchars($r['response']) ?></p>
            </div>
        </div>
    <?php endforeach; ?>

<?php else: ?>
    <p>No reservations yet.</p>
<?php endif; ?>

<script>
function toggleDetails(block) {
    const details = block.querySelector(".details");
    details.style.display = details.style.display === "block" ? "none" : "block";
}

// SEARCH + FILTER
const searchBox = document.getElementById("searchBox");
const filterResponse = document.getElementById("filterResponse");
const filterRole = document.getElementById("filterRole");
const blocks = document.querySelectorAll(".rsvp-block");

function filterList() {
    let search = searchBox.value.toLowerCase();
    let response = filterResponse.value;
    let role = filterRole.value;

    blocks.forEach(b => {
        let matchesSearch = b.dataset.name.includes(search);
        let matchesResponse = response === "" || b.dataset.response === response;
        let matchesRole = role === "" || b.dataset.role === role;

        b.style.display = (matchesSearch && matchesResponse && matchesRole) ? "block" : "none";
    });
}

searchBox.addEventListener("input", filterList);
filterResponse.addEventListener("change", filterList);
filterRole.addEventListener("change", filterList);
</script>

</body>
</html>
