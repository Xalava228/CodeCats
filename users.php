<?php
session_start();
require_once 'crypto.php';

// Доступ только для админа
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.html");
    exit;
}

// Загружаем всех пользователей из users.json
$usersFile = 'users.json';
$allUsers = [];
if (file_exists($usersFile)) {
    $content = file_get_contents($usersFile);
    $allUsers = json_decode($content, true);
    if (!is_array($allUsers)) {
        $allUsers = [];
    }
}

// Функция для дешифрования мастер-копии с использованием MASTER_KEY
function decryptMaster($data) {
    return decryptData($data, MASTER_KEY);
}

// Список ачивок для выбора
$achievementOptions = [
    "🥇 Золотой старт",
    "🏆 Организатор месяца",
    "🥈 Серебряный участник",
    "🥉 Бронзовый участник",
    "🎖 Победитель хакатона",
    "🏅 Лучший слушатель",
    "🎗 Активный участник",
    "🏆 Лидер сообщества"
];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Список пользователей</title>
    <link rel="stylesheet" href="css/profile.css">
    <style>
        .users-container {
            max-width: 1000px;
            margin: 40px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .users-container h1 {
            text-align: center;
            color: #335ebd;
        }
        .users-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .users-table th, .users-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }
        .users-table th {
            background-color: #335ebd;
            color: #fff;
        }
        .update-form select {
            padding: 5px;
            border-radius: 3px;
            border: 1px solid #ccc;
        }
        .update-form button {
            padding: 5px 10px;
            background-color: #335ebd;
            color: #fff;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .update-form button:hover {
            background-color: #264f9d;
        }
        .back-button {
            display: inline-block;
            text-decoration: none;
            background-color: #335ebd;
            color: #fff;
            padding: 10px 20px;
            border-radius: 5px;
            margin-top: 20px;
            transition: background-color 0.3s ease;
        }
        .back-button:hover {
            background-color: #264f9d;
        }
    </style>
</head>
<body>
<div class="users-container">
    <h1>Список пользователей</h1>
    <table class="users-table">
        <thead>
        <tr>
            <th>Email</th>
            <th>Логин</th>
            <th>Имя</th>
            <th>Фамилия</th>
            <th>Город</th>
            <th>Роль</th>
            <th>Ачивки</th>
            <th>Действие</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($allUsers as $usr): 
            $login = isset($usr['login_master']) ? decryptMaster($usr['login_master']) : 'N/A';
            $firstName = isset($usr['firstName_master']) ? decryptMaster($usr['firstName_master']) : 'N/A';
            $lastName = isset($usr['lastName_master']) ? decryptMaster($usr['lastName_master']) : 'N/A';
            $city = isset($usr['city_master']) ? decryptMaster($usr['city_master']) : 'Не указан';
            $role = $usr['role'] ?? 'member';
            $achievements = !empty($usr['achievements']) ? implode(', ', $usr['achievements']) : 'Нет';
        ?>
        <tr>
            <td><?php echo htmlspecialchars($usr['email'], ENT_QUOTES, 'UTF-8'); ?></td>
            <td><?php echo htmlspecialchars($login, ENT_QUOTES, 'UTF-8'); ?></td>
            <td><?php echo htmlspecialchars($firstName, ENT_QUOTES, 'UTF-8'); ?></td>
            <td><?php echo htmlspecialchars($lastName, ENT_QUOTES, 'UTF-8'); ?></td>
            <td><?php echo htmlspecialchars($city, ENT_QUOTES, 'UTF-8'); ?></td>
            <td><?php echo htmlspecialchars($role, ENT_QUOTES, 'UTF-8'); ?></td>
            <td><?php echo htmlspecialchars($achievements, ENT_QUOTES, 'UTF-8'); ?></td>
            <td>
                <form class="update-form" method="POST" action="update_user.php">
                    <input type="hidden" name="email" value="<?php echo htmlspecialchars($usr['email'], ENT_QUOTES, 'UTF-8'); ?>">
                    <select name="role" required>
                        <option value="member" <?php if($role==='member') echo 'selected'; ?>>member</option>
                        <option value="organazer" <?php if($role==='organazer') echo 'selected'; ?>>organazer</option>
                        <option value="admin" <?php if($role==='admin') echo 'selected'; ?>>admin</option>
                    </select>
                    <select name="achievement">
                        <option value="">-- Добавить ачивку --</option>
                        <?php foreach ($achievementOptions as $achOpt): ?>
                            <option value="<?php echo htmlspecialchars($achOpt, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($achOpt, ENT_QUOTES, 'UTF-8'); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit">Обновить</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <a href="profile.php" class="back-button">Вернуться в профиль</a>
</div>
</body>
</html>
