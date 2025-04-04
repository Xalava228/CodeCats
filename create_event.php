<?php
session_start();
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['organazer', 'admin'])) {
    header("Location: login.html");
    exit;
}

$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$date = trim($_POST['date'] ?? '');
$place = trim($_POST['place'] ?? '');
$tags = trim($_POST['tags'] ?? '');
$logoPath = '';

if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
    $tmpName = $_FILES['logo']['tmp_name'];
    $originalName = basename($_FILES['logo']['name']);
    $uploadDir = 'data/img/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    $targetPath = $uploadDir . time() . '_' . $originalName;
    if (move_uploaded_file($tmpName, $targetPath)) {
        $logoPath = $targetPath;
    }
}

if (!$title || !$description || !$date || !$place) {
    $_SESSION['message'] = "Пожалуйста, заполните все обязательные поля.";
    header("Location: profile.php");
    exit;
}

$eventsFile = 'data/events.json';
$eventsData = [];
if (file_exists($eventsFile)) {
    $content = file_get_contents($eventsFile);
    $eventsData = json_decode($content, true);
    if (!is_array($eventsData)) {
        $eventsData = [];
    }
}

$newId = 1;
if (!empty($eventsData)) {
    $ids = array_column($eventsData, 'id');
    $newId = max($ids) + 1;
}

$newEvent = [
    'id' => $newId,
    'title' => $title,
    'description' => $description,
    'date' => $date,
    'place' => $place,
    'tags' => $tags,
    'logo' => $logoPath,
    'status' => 'pending', // Ожидает модерации администратором
    'createdBy' => $_SESSION['user']['email']
];

$eventsData[] = $newEvent;
file_put_contents($eventsFile, json_encode($eventsData, JSON_PRETTY_PRINT));

$_SESSION['message'] = "Мероприятие создано и отправлено на модерацию.";
header("Location: profile.php");
exit;
?>
