<?php
require_once 'config.php';

// 1. Add ticket_id to registrations if it doesn't exist
$check_col = $conn->query("SHOW COLUMNS FROM registrations LIKE 'ticket_id'");
if ($check_col->num_rows == 0) {
    $conn->query("ALTER TABLE registrations ADD ticket_id VARCHAR(50) NULL AFTER event_id");
    
    // Generate ticket_id for existing registrations
    $existing = $conn->query("SELECT id FROM registrations");
    while($row = $existing->fetch_assoc()) {
        $t_id = 'SEC-' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
        $conn->query("UPDATE registrations SET ticket_id = '$t_id' WHERE id = " . $row['id']);
    }
    
    // Make it UNIQUE after generating for existing rows (optional, but good practice)
    $conn->query("ALTER TABLE registrations ADD UNIQUE(ticket_id)");
    echo "Column ticket_id added to registrations.\n";
} else {
    echo "Column ticket_id already exists in registrations.\n";
}

// 2. Create cs_messages table
$sql = "CREATE TABLE IF NOT EXISTS cs_messages (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    ticket_id VARCHAR(50) NOT NULL,
    sender_type ENUM('user', 'admin') NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES registrations(ticket_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

if ($conn->query($sql)) {
    echo "Table cs_messages created or already exists.\n";
} else {
    echo "Error creating table cs_messages: " . $conn->error . "\n";
}
?>
