<?php
require_once __DIR__ . '/../config.php';
requireLogin();

// Returns JSON for FullCalendar events for the logged-in user's planned outfits
header('Content-Type: application/json');

$start = $_GET['start'] ?? null; // fullcalendar passes ISO strings
$end = $_GET['end'] ?? null;

$params = [$_SESSION['user_id']];
$sql = 'SELECT p.id AS plan_id, p.planned_for, p.note, p.season_hint, o.id AS outfit_id, o.title,
               t.image_path AS top_image, b.image_path AS bottom_image, s.image_path AS shoe_image, a.image_path AS accessory_image
        FROM '. TBL_OUTFITS .'_planned p
        JOIN '. TBL_OUTFITS .' o ON p.outfit_id = o.id
        LEFT JOIN '. TBL_CLOTHES .' t ON o.top_id = t.id
        LEFT JOIN '. TBL_CLOTHES .' b ON o.bottom_id = b.id
        LEFT JOIN '. TBL_CLOTHES .' s ON o.shoe_id = s.id
        LEFT JOIN '. TBL_CLOTHES .' a ON o.accessory_id = a.id
        WHERE p.user_id = ?';

if ($start && $end) {
    $sql .= ' AND p.planned_for >= ? AND p.planned_for <= ?';
    $params[] = date('Y-m-d', strtotime($start));
    $params[] = date('Y-m-d', strtotime($end));
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$events = [];
foreach ($rows as $r) {
    $title = $r['title'] ?: 'Outfit';
    $img = $r['top_image'] ?: $r['bottom_image'] ?: $r['shoe_image'] ?: $r['accessory_image'] ?: '';
    $events[] = [
        'id' => (int)$r['plan_id'],
        'title' => $title,
        'start' => $r['planned_for'],
        'allDay' => true,
        'extendedProps' => [
            'outfit_id' => (int)$r['outfit_id'],
            'note' => $r['note'],
            'season_hint' => $r['season_hint'],
            'image' => $img,
        ],
    ];
}

echo json_encode($events);
exit;
