<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.html");
    exit;
}

$targetEmail = trim($_POST['email'] ?? '');
$newRole = trim($_POST['role'] ?? '');
$allowedRoles = ['member', 'organazer', 'admin'];
if (!in_array($newRole, $allowedRoles)) {
    $_SESSION['message'] = "Неверная роль.";
    header("Location: profile.php");
    exit;
}

$usersFile = 'users.json';
$usersData = [];
if (file_exists($usersFile)) {
    $content = file_get_contents($usersFile);
    $usersData = json_decode($content, true);
    if (!is_array($usersData)) { $usersData = []; }
}

$updated = false;
foreach ($usersData as &$usr) {
    if ($usr['email'] === $targetEmail) {
        $usr['role'] = $newRole;
        $updated = true;
        break;
    }
}
if ($updated) {
    file_put_contents($usersFile, json_encode($usersData, JSON_PRETTY_PRINT));
    $_SESSION['message'] = "Роль пользователя $targetEmail изменена на $newRole.";
} else {
    $_SESSION['message'] = "Пользователь не найден.";
}
header("Location: profile.php");
exit;
?>
