<?php
session_start();
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['organazer', 'admin'])) {
    header("Location: login.html");
    exit;
}

$requestId = intval($_POST['request_id'] ?? 0);
$action = $_POST['action'] ?? '';

if (!$requestId || !in_array($action, ['approve', 'decline'])) {
    $_SESSION['message'] = "Неверные данные запроса.";
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

$updated = false;
foreach ($requestsData as &$request) {
    if ($request['id'] === $requestId) {
        $request['status'] = ($action === 'approve') ? 'approved' : 'declined';
        $updated = true;
        break;
    }
}
if ($updated) {
    file_put_contents($requestsFile, json_encode($requestsData, JSON_PRETTY_PRINT));
    $_SESSION['message'] = "Заявка обновлена.";
} else {
    $_SESSION['message'] = "Заявка не найдена.";
}

header("Location: profile.php");
exit;
?>
