<?php
require_once 'config.php';

// 1. Drop foreign key constraint from cs_messages
$sql_drop_fk = "ALTER TABLE cs_messages DROP FOREIGN KEY cs_messages_ibfk_1";
if ($conn->query($sql_drop_fk)) {
    echo "Foreign key cs_messages_ibfk_1 dropped successfully.<br>";
} else {
    echo "Error dropping foreign key (might not exist): " . $conn->error . "<br>";
}

// 2. Change column name from ticket_id to cs_ticket_id
$sql_change_col = "ALTER TABLE cs_messages CHANGE ticket_id cs_ticket_id VARCHAR(50) NOT NULL";
if ($conn->query($sql_change_col)) {
    echo "Column ticket_id renamed to cs_ticket_id.<br>";
} else {
    echo "Error renaming column: " . $conn->error . "<br>";
}

// 3. Add user_name column to cs_messages to store the name of the user
$sql_add_col = "ALTER TABLE cs_messages ADD user_name VARCHAR(100) NULL AFTER cs_ticket_id";
if ($conn->query($sql_add_col)) {
    echo "Column user_name added to cs_messages.<br>";
} else {
    echo "Error adding column (might already exist): " . $conn->error . "<br>";
}

echo "<br><b>Migration completed!</b>";
?>
