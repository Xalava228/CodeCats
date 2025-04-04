<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.html");
    exit;
}

$targetEmail = trim($_POST['email'] ?? '');
$newRole = trim($_POST['role'] ?? '');
$newAchievement = trim($_POST['achievement'] ?? '');

$allowedRoles = ['member', 'organazer', 'admin'];
if (!in_array($newRole, $allowedRoles)) {
    $_SESSION['message'] = "Неверная роль.";
    header("Location: users.php");
    exit;
}

$usersFile = 'users.json';
$usersData = [];
if (file_exists($usersFile)) {
    $content = file_get_contents($usersFile);
    $usersData = json_decode($content, true);
    if (!is_array($usersData)) {
        $usersData = [];
    }
}

$updated = false;
foreach ($usersData as &$usr) {
    if ($usr['email'] === $targetEmail) {
        // Обновляем роль
        $usr['role'] = $newRole;

        // Если выбрана ачивка и её ещё нет у пользователя, добавляем
        if ($newAchievement !== "" && (!isset($usr['achievements']) || !in_array($newAchievement, $usr['achievements']))) {
            if (!isset($usr['achievements']) || !is_array($usr['achievements'])) {
                $usr['achievements'] = [];
            }
            $usr['achievements'][] = $newAchievement;
        }

        $updated = true;
        break;
    }
}



if ($_SESSION['user']['email'] === $targetEmail) {
    foreach ($usersData as $usr) {
        if ($usr['email'] === $targetEmail) {
            $_SESSION['user'] = $usr;
            break;
        }
    }
}



if ($updated) {
    file_put_contents($usersFile, json_encode($usersData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    $_SESSION['message'] = "Пользователь $targetEmail обновлён.";
} else {
    $_SESSION['message'] = "Пользователь не найден.";
}

header("Location: users.php");
exit;
