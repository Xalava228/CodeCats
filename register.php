<?php
// register.php
session_start();
require_once 'crypto.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получаем данные формы
    $firstName = $_POST['firstName'] ?? '';
    $lastName  = $_POST['lastName'] ?? '';
    $phone     = $_POST['phone'] ?? '';
    $email     = $_POST['email'] ?? '';
    $city      = $_POST['city'] ?? '';
    $login     = $_POST['login'] ?? '';
    $password  = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['passwordConfirm'] ?? '';
    
    if ($password !== $passwordConfirm) {
        echo json_encode(['status' => 'error', 'message' => 'Пароли не совпадают']);
        exit;
    }
    
    // Генерируем случайную соль (16 байт в hex)
    $salt = bin2hex(random_bytes(16));
    // Генерируем ключ на основе пароля и соли
    $key = generateEncryptionKey($password, $salt);
    
    // Шифруем данные с пользовательским ключом
    $encryptedFirstName = encryptData($firstName, $key);
    $encryptedLastName  = encryptData($lastName, $key);
    $encryptedPhone     = encryptData($phone, $key);
    $encryptedCity      = encryptData($city, $key);
    $encryptedLogin     = encryptData($login, $key);
    $encryptedEmail     = encryptData($email, $key);
    
    // Шифруем мастер‑копии с MASTER_KEY
    $encryptedFirstNameMaster = encryptData($firstName, MASTER_KEY);
    $encryptedLastNameMaster  = encryptData($lastName, MASTER_KEY);
    $encryptedPhoneMaster     = encryptData($phone, MASTER_KEY);
    $encryptedCityMaster      = encryptData($city, MASTER_KEY);
    $encryptedLoginMaster     = encryptData($login, MASTER_KEY);
    $encryptedEmailMaster     = encryptData($email, MASTER_KEY);
    
    // Хэшируем пароль для аутентификации
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    
    // Генерируем токен подтверждения регистрации
    $tokenRaw = bin2hex(random_bytes(16));
    $tokenHash = hash('sha256', $tokenRaw);
    
    // Формируем запись нового пользователя
    $newUser = [
        'firstName'           => $encryptedFirstName,
        'lastName'            => $encryptedLastName,
        'phone'               => $encryptedPhone,
        'email'               => $email,               // открытый email для поиска
        'encryptedEmail'      => $encryptedEmail,
        'city'                => $encryptedCity,
        'login'               => $encryptedLogin,
        'password'            => $passwordHash,
        'salt'                => $salt,
        'confirmed'           => false,                // подтверждение не пройдено
        'token'               => $tokenHash,
        'achievements'        => [],
        'eventsCount'         => 0,
        'role'                => 'member',
        // Мастер‑копии для аналитики:
        'firstName_master'    => $encryptedFirstNameMaster,
        'lastName_master'     => $encryptedLastNameMaster,
        'phone_master'        => $encryptedPhoneMaster,
        'city_master'         => $encryptedCityMaster,
        'login_master'        => $encryptedLoginMaster,
        'email_master'        => $encryptedEmailMaster,
    ];
    
    $usersFile = 'users.json';
    $usersData = [];
    if (file_exists($usersFile)) {
        $content = file_get_contents($usersFile);
        $usersData = json_decode($content, true);
        if (!is_array($usersData)) {
            $usersData = [];
        }
    }
    
    // Проверяем наличие пользователя с таким email
    foreach ($usersData as $user) {
        if ($user['email'] === $email) {
            echo json_encode(['status' => 'error', 'message' => 'Пользователь с таким email уже существует']);
            exit;
        }
    }
    
    $usersData[] = $newUser;
    file_put_contents($usersFile, json_encode($usersData, JSON_PRETTY_PRINT));
    
    // Отправка письма с подтверждением регистрации
    $subject = "Подтверждение регистрации";
    $confirmLink = "https://minamur.ru/confirm.php?token=" . $tokenRaw;
    $message = "Для подтверждения регистрации перейдите по ссылке: <a href='{$confirmLink}'>Подтвердить регистрацию</a>";
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=utf-8\r\n";
    $headers .= "From: no-reply@minamur.ru\r\n";
    mail($email, $subject, $message, $headers);
    
    echo json_encode(['status' => 'success', 'message' => 'Регистрация успешна. Проверьте почту для подтверждения регистрации.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Неверный метод запроса']);
}
?>
