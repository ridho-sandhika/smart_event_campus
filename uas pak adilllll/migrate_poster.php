<?php
require_once 'config.php';

$sql = "ALTER TABLE events ADD COLUMN poster VARCHAR(255) DEFAULT NULL";
if ($conn->query($sql) === TRUE) {
    echo "Column 'poster' added successfully.";
} else {
    echo "Error updating table: " . $conn->error;
}
?>
