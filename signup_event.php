<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.html");
    exit;
}

$eventDate = $_GET['date'] ?? '';
$eventTitle = $_GET['title'] ?? '';
if (!$eventDate || !$eventTitle) {
    $_SESSION['message'] = "Некорректные данные мероприятия.";
    header("Location: profile.php");
    exit;
}

$requestsFile = 'data/requests.json';
$requestsData = [];
if (file_exists($requestsFile)) {
    $content = file_get_contents($requestsFile);
    $requestsData = json_decode($content, true);
    if (!is_array($requestsData)) {
        $requestsData = [];
    }
}

$newId = 1;
if (!empty($requestsData)) {
    $ids = array_column($requestsData, 'id');
    $newId = max($ids) + 1;
}

$newRequest = [
    'id' => $newId,
    'participantEmail' => $_SESSION['user']['email'],
    'eventDate' => $eventDate,
    'eventTitle' => $eventTitle,
    'status' => 'pending'  // Заявка на участие, ожидает одобрения
];

$requestsData[] = $newRequest;
file_put_contents($requestsFile, json_encode($requestsData, JSON_PRETTY_PRINT));

$_SESSION['message'] = "Ваша заявка на участие в мероприятии отправлена на одобрение.";
header("Location: profile.php");
exit;
?>
