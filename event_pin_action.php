<?php
    session_start();

    if (!isset($_SESSION["userID"])) {
        echo "NO_USER";
        exit;
    }

    $userID = intval($_SESSION["userID"]);
    $eventID = intval($_POST["eventID"]);
    $action = $_POST["action"] ?? "";

    $conn = new mysqli("localhost", "root", "", "grizz_pop_database");

    if ($conn->connect_error) {
        echo "DB_ERROR";
        exit;
    }

    if ($action === "pin") {
        // Prevent duplicates by using INSERT IGNORE
        $sql = "INSERT IGNORE INTO pinned_events (userID, eventID)
                VALUES ($userID, $eventID)";
    }
    elseif ($action === "unpin") {
        $sql = "DELETE FROM pinned_events
                WHERE userID = $userID AND eventID = $eventID";
    }
    else {
        echo "BAD_ACTION";
        exit;
    }

    if ($conn->query($sql)) {
        echo "OK";
    } else {
        echo "ERROR";
    }
?>
