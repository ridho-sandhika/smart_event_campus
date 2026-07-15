<?php
require_once 'config.php';

// Backend AJAX untuk Live Search & Filter
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter = isset($_GET['filter']) ? trim($_GET['filter']) : '';

$sql = "SELECT * FROM events WHERE event_date >= CURDATE()";
$params = [];
$types = '';

if (!empty($search)) {
    $sql .= " AND (title LIKE ? OR description LIKE ? OR location LIKE ?)";
    $like = "%$search%";
    $params = [$like, $like, $like];
    $types = 'sss';
}
if (!empty($filter) && in_array($filter, ['Seminar', 'Workshop', 'Lomba', 'Pelatihan'])) {
    $sql .= " AND event_type = ?";
    $params[] = $filter;
    $types .= 's';
}
$sql .= " ORDER BY event_date ASC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$output = '';
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $date = date("d F Y", strtotime($row['event_date']));
        $time = date("H:i", strtotime($row['event_time']));
        
        // Google Calendar URL
        $cal_start = date("Ymd\THis\Z", strtotime($row['event_date'] . ' ' . $row['event_time']));
        $cal_end   = date("Ymd\THis\Z", strtotime($row['event_date'] . ' ' . $row['event_time']) + 7200);
        $cal_url   = "https://calendar.google.com/calendar/render?action=TEMPLATE&text=" . urlencode($row['title']) . "&dates={$cal_start}/{$cal_end}&details=" . urlencode($row['description']) . "&location=" . urlencode($row['location']);

        $output .= '<div class="event-card glass">';
        $output .= '<div class="event-content">';
        $output .= '<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">';
        $output .= '<span class="event-badge">' . htmlspecialchars($row['event_type']) . '</span>';
        if($row['is_paid']) {
            $output .= '<span style="color:#10b981; font-weight:bold; font-size:1.1rem;">Rp ' . number_format($row['price'],0,',','.') . '</span>';
        } else {
            $output .= '<span style="color:#10b981; font-weight:bold; font-size:1.1rem;">GRATIS</span>';
        }
        $output .= '</div>';
        $output .= '<h3 class="event-title">' . htmlspecialchars($row['title']) . '</h3>';
        $output .= '<div class="event-meta">';
        $output .= '<span><i class="fa-regular fa-calendar"></i> ' . $date . '</span>';
        $output .= '<span><i class="fa-regular fa-clock"></i> ' . $time . ' WIB</span>';
        $output .= '<span><i class="fa-solid fa-location-dot"></i> ' . htmlspecialchars($row['location']) . '</span>';
        if($row['max_participants'] > 0) {
            $output .= '<span><i class="fa-solid fa-users"></i> Kuota: ' . $row['max_participants'] . '</span>';
        }
        $output .= '</div>';
        $output .= '<p class="event-desc">' . htmlspecialchars($row['description']) . '</p>';
        $output .= '<div style="display: flex; gap: 0.75rem; flex-wrap: wrap; margin-top: 1rem;">';
        $output .= '<a href="register_event.php?id=' . $row['id'] . '" class="btn btn-primary" style="flex: 100%; justify-content: center;"><i class="fa-solid fa-ticket"></i> Daftar Sekarang</a>';
        if (!empty($row['maps_url'])) {
            $output .= '<a href="' . htmlspecialchars($row['maps_url']) . '" target="_blank" class="btn btn-outline" style="flex: 1; justify-content: center; border-color: var(--secondary); color: var(--secondary);"><i class="fa-solid fa-map-location-dot"></i> Maps</a>';
        }
        $output .= '<a href="' . $cal_url . '" target="_blank" class="btn btn-outline" style="flex: 1; justify-content: center; border-color: #06b6d4; color: #06b6d4;"><i class="fa-solid fa-calendar-plus"></i> Kalender</a>';
        if (!empty($row['material_file'])) {
            $output .= '<a href="uploads/materials/' . htmlspecialchars($row['material_file']) . '" target="_blank" class="btn btn-outline" style="flex: 100%; justify-content: center; border-color: var(--success); color: var(--success); margin-top: 0.5rem;"><i class="fa-solid fa-download"></i> Unduh Materi</a>';
        }
        $output .= '</div>';
        $output .= '</div>';
        $output .= '</div>';
    }
} else {
    $output = '<div class="glass" style="grid-column: 1/-1; text-align: center; padding: 4rem; border-radius: 1rem;"><i class="fa-solid fa-magnifying-glass" style="font-size: 3rem; color: var(--text-muted); display: block; margin-bottom: 1rem;"></i><p style="font-size: 1.25rem; color: var(--text-muted);">Tidak ada kegiatan yang sesuai pencarian.</p></div>';
}

echo $output;
?>
