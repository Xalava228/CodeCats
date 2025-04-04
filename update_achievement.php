<?php
// update_achievement.php
session_start();
require_once 'crypto.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Доступ запрещен']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['login'], $data['achievement'])) {
    echo json_encode(['status' => 'error', 'message' => 'Неверные данные']);
    exit;
}

$loginToUpdate = $data['login'];
$achievementToAssign = $data['achievement'];

$usersFile = 'users.json';
if (!file_exists($usersFile)) {
    echo json_encode(['status' => 'error', 'message' => 'Файл пользователей не найден']);
    exit;
}

$usersData = json_decode(file_get_contents($usersFile), true);
if (!is_array($usersData)) {
    echo json_encode(['status' => 'error', 'message' => 'Ошибка данных пользователей']);
    exit;
}

$updated = false;
foreach ($usersData as &$user) {
    // Сравнение за счет дешифрования зашифрованного логина
    if (decryptData($user['login']) === $loginToUpdate) {
        if (!isset($user['achievements']) || !is_array($user['achievements'])) {
            $user['achievements'] = [];
        }
        if (!in_array($achievementToAssign, $user['achievements'])) {
            $user['achievements'][] = $achievementToAssign;
            $updated = true;
        }
        break;
    }
}

if (!$updated) {
    echo json_encode(['status' => 'error', 'message' => 'Пользователь не найден или ачивка уже назначена']);
    exit;
}

if (file_put_contents($usersFile, json_encode($usersData, JSON_PRETTY_PRINT), LOCK_EX) === false) {
    echo json_encode(['status' => 'error', 'message' => 'Ошибка сохранения данных']);
    exit;
}

echo json_encode(['status' => 'success', 'message' => 'Ачивка назначена успешно']);
?>
