<?php
// login.php
session_start();
require_once 'crypto.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['email'], $data['password'])) {
    echo json_encode(['status' => 'error', 'message' => 'Не указан email или пароль.']);
    exit;
}

$email = trim($data['email']);
$password = $data['password'];

$usersFile = 'users.json';
if (!file_exists($usersFile)) {
    echo json_encode(['status' => 'error', 'message' => 'Пользователи отсутствуют.']);
    exit;
}

$usersData = json_decode(file_get_contents($usersFile), true);
if (!is_array($usersData)) {
    echo json_encode(['status' => 'error', 'message' => 'Ошибка данных пользователей.']);
    exit;
}

$userFound = null;
foreach ($usersData as $user) {
    if ($user['email'] === $email) {
        $userFound = $user;
        break;
    }
}

if (!$userFound) {
    echo json_encode(['status' => 'error', 'message' => 'Пользователь не найден.']);
    exit;
}

if (!password_verify($password, $userFound['password'])) {
    echo json_encode(['status' => 'error', 'message' => 'Неверный пароль.']);
    exit;
}

// Проверяем, подтверждена ли регистрация
if (!isset($userFound['confirmed']) || $userFound['confirmed'] !== true) {
    echo json_encode(['status' => 'error', 'message' => 'Регистрация не подтверждена.']);
    exit;
}

// Генерируем симметричный ключ из пароля и сохранённой соли
$key = generateEncryptionKey($password, $userFound['salt']);

// Сохраняем данные пользователя и ключ в сессии
$_SESSION['user'] = $userFound;
// Сохраняем ключ в сессии (base64-кодируем его для удобства)
$_SESSION['encryption_key'] = base64_encode($key);

echo json_encode(['status' => 'success', 'message' => 'Вход выполнен успешно.']);
?>
