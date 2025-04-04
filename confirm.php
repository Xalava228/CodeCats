<?php
// confirm.php
session_start();

if (isset($_GET['token'])) {
    $tokenRaw = $_GET['token'];
    $tokenHash = hash('sha256', $tokenRaw);
    
    $usersFile = 'users.json';
    if (!file_exists($usersFile)) {
        echo "Файл пользователей не найден.";
        exit;
    }
    
    $usersData = json_decode(file_get_contents($usersFile), true);
    $found = false;
    
    foreach ($usersData as &$user) {
        if ($user['token'] === $tokenHash) {
            $user['confirmed'] = true;
            $found = true;
            // Если пользователь уже залогинен, можно обновить сессию
            if (isset($_SESSION['user']) && $_SESSION['user']['email'] === $user['email']) {
                $_SESSION['user'] = $user;
            }
            break;
        }
    }
    
    if ($found) {
        file_put_contents($usersFile, json_encode($usersData, JSON_PRETTY_PRINT));
        header("Refresh: 3; url=login.html");
        echo "Регистрация успешно подтверждена. Через 3 секунды вы будете перенаправлены на страницу входа.";
    } else {
        echo "Неверный токен или пользователь не найден.";
    }
} else {
    echo "Токен не передан.";
}
?>
