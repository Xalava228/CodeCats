<?php
session_start();
require_once 'crypto.php';

if (!isset($_SESSION['user']) || !isset($_SESSION['encryption_key'])) {
    header("Location: login.html");
    exit;
}

$user = $_SESSION['user'];
$key = base64_decode($_SESSION['encryption_key']);

// Если регистрация не подтверждена – перенаправляем на вход
if (!isset($user['confirmed']) || $user['confirmed'] !== true) {
    session_destroy();
    header("Location: login.html?error=not_confirmed");
    exit;
}

// Расшифровываем данные с использованием пользовательского ключа
$decryptedUser = [
    'firstName' => isset($user['firstName']) ? decryptData($user['firstName'], $key) : '',
    'lastName' => isset($user['lastName']) ? decryptData($user['lastName'], $key) : '',
    'phone' => isset($user['phone']) ? decryptData($user['phone'], $key) : '',
    'email' => $user['email'] ?? '',
    'city' => isset($user['city']) ? decryptData($user['city'], $key) : '',
    'login' => isset($user['login']) ? decryptData($user['login'], $key) : '',
    'role' => $user['role'] ?? 'member',
    'achievements' => $user['achievements'] ?? [],
    'eventsCount' => $user['eventsCount'] ?? 0
];

// Если нет ачивок, добавляем дефолтную ачивку за регистрацию
if (empty($decryptedUser['achievements'])) {
    $decryptedUser['achievements'][] = "🥇 Золотой старт";
}

// Загружаем список мероприятий (из файла data/events.json)
$eventsFile = 'data/events.json';
$eventsData = [];
if (file_exists($eventsFile)) {
    $eventsData = json_decode(file_get_contents($eventsFile), true);
    if (!is_array($eventsData)) {
        $eventsData = [];
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Профиль пользователя</title>
    <link rel="stylesheet" href="css/profile.css">
    <style>
        /* Стили для кнопок и дополнительного блока */
        .header-buttons a {
            text-decoration: none;
            background-color: #335ebd;
            color: #fff;
            padding: 10px 20px;
            border-radius: 5px;
            margin: 5px;
            transition: background-color 0.3s ease;
        }
        .header-buttons a:hover {
            background-color: #264f9d;
        }
        .back-main {
            margin-top: 10px;
            display: inline-block;
        }
        /* Стили для организаторской панели */
        .organizer-panel {
            margin-top: 40px;
            padding: 15px;
            border: 1px solid #ccc;
            border-radius: 10px;
            background-color: #fefefe;
        }
        .organizer-panel h2 {
            text-align: center;
            color: #335ebd;
            margin-bottom: 20px;
        }
        .organizer-panel form {
            margin-bottom: 20px;
        }
        .organizer-panel label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .organizer-panel input,
        .organizer-panel textarea,
        .organizer-panel select {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .organizer-panel button {
            padding: 10px;
            background-color: #335ebd;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .organizer-panel button:hover {
            background-color: #264f9d;
        }
        /* Стили для таблицы мероприятий в профиле */
        .events-list {
            margin-top: 30px;
            padding: 0 15px;
        }
        .events-list h2 {
            text-align: center;
            font-size: 2em;
            color: #335ebd;
            margin-bottom: 20px;
        }
        .event-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background-color: #fff;
            border: 1px solid #ccc;
            border-radius: 8px;
            margin-bottom: 15px;
            padding: 15px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .event-details {
            flex: 1;
            margin-right: 15px;
        }
        .event-details strong {
            display: block;
            font-size: 1.2em;
            color: #335ebd;
            margin-bottom: 8px;
        }
        .event-details small {
            font-size: 0.9em;
            color: #777;
        }
        .signup-button {
            background-color: #335ebd;
            color: #fff;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: bold;
            transition: background-color 0.3s ease;
            white-space: nowrap;
        }
        .signup-button:hover {
            background-color: #264f9d;
        }
        /* Стили для ачивок */
        .achievements-list {
            margin-top: 20px;
        }
        .achievement-item {
            display: inline-block;
            background-color: #fffbcc;
            border: 1px solid #ffe58f;
            padding: 5px 10px;
            border-radius: 20px;
            margin-right: 5px;
            margin-bottom: 5px;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
<div class="profile-container">
    <?php if (isset($_SESSION['message'])): ?>
        <div class="notification">
            <?php echo htmlspecialchars($_SESSION['message'], ENT_QUOTES, 'UTF-8');
            unset($_SESSION['message']); ?>
        </div>
    <?php endif; ?>

    <header class="profile-header">
        <h1>Добро пожаловать, <?php echo htmlspecialchars($decryptedUser['firstName'] . ' ' . $decryptedUser['lastName'], ENT_QUOTES, 'UTF-8'); ?>!</h1>
        <p class="role">Роль: <?php echo htmlspecialchars($decryptedUser['role'], ENT_QUOTES, 'UTF-8'); ?></p>
        <div class="header-buttons">
            <?php if ($decryptedUser['role'] === 'admin'): ?>
                <a href="analytics.php" class="admin-button">Аналитика</a>
				<a href="users.php" class="admin-button">Управление пользователями</a>
            <?php endif; ?>
            <a href="logout.php" class="logout-button">Выйти</a>
            <a href="index.html" class="back-main">Главная страница</a>
        </div>
    </header>

    <section class="profile-info">
        <div class="info-item">
            <label>Email:</label>
            <span><?php echo htmlspecialchars($decryptedUser['email'], ENT_QUOTES, 'UTF-8'); ?></span>
        </div>
        <div class="info-item">
            <label>Логин:</label>
            <span><?php echo htmlspecialchars($decryptedUser['login'], ENT_QUOTES, 'UTF-8'); ?></span>
        </div>
        <div class="info-item">
            <label>Телефон:</label>
            <span><?php echo htmlspecialchars($decryptedUser['phone'], ENT_QUOTES, 'UTF-8'); ?></span>
        </div>
        <div class="info-item">
            <label>Город:</label>
            <span><?php echo htmlspecialchars($decryptedUser['city'], ENT_QUOTES, 'UTF-8'); ?></span>
        </div>
    </section>

    <section class="achievements-section">
        <h2>Ачивки</h2>
        <div class="achievements-list">
            <?php foreach ($decryptedUser['achievements'] as $ach): ?>
                <span class="achievement-item"><?php echo htmlspecialchars($ach, ENT_QUOTES, 'UTF-8'); ?></span>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="events-list">
        <h2>Мероприятия</h2>
        <?php if (!empty($eventsData)): ?>
            <?php foreach ($eventsData as $event): ?>
                <div class="event-item">
                    <div class="event-details">
                        <strong><?php echo htmlspecialchars($event['title'], ENT_QUOTES, 'UTF-8'); ?></strong>
                        <small><?php echo htmlspecialchars($event['date'], ENT_QUOTES, 'UTF-8'); ?>, <?php echo htmlspecialchars($event['place'], ENT_QUOTES, 'UTF-8'); ?></small>
                    </div>
                    <div>
                        <a href="signup_event.php?date=<?php echo urlencode($event['date']); ?>&title=<?php echo urlencode($event['title']); ?>" class="signup-button">Записаться</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Нет доступных мероприятий.</p>
        <?php endif; ?>
    </section>

    <?php if ($decryptedUser['role'] === 'organazer'): ?>
        <section class="organizer-panel">
            <h2>Панель управления для организаторов</h2>

            <h3>Создание мероприятия</h3>
            <form method="POST" action="create_event.php" enctype="multipart/form-data">
                <label>Название мероприятия:</label>
                <input type="text" name="title" required>
                <label>Описание мероприятия:</label>
                <textarea name="description" required></textarea>
                <label>Дата мероприятия:</label>
                <input type="date" name="date" required>
                <label>Место проведения:</label>
                <input type="text" name="place" required>
                <label>Теги (через запятую):</label>
                <input type="text" name="tags">
                <label>Загрузить логотип мероприятия:</label>
                <input type="file" name="logo">
                <button type="submit">Создать мероприятие</button>
            </form>

            <h3>Управление заявками на участие</h3>
            <table class="requests-table">
                <thead>
                <tr>
                    <th>Участник</th>
                    <th>Мероприятие</th>
                    <th>Статус</th>
                    <th>Действие</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>participant@example.com</td>
                    <td>Название мероприятия</td>
                    <td>Ожидание</td>
                    <td>
                        <form method="POST" action="manage_request.php" style="display:inline;">
                            <input type="hidden" name="request_id" value="123">
                            <button type="submit" name="action" value="approve">Одобрить</button>
                        </form>
                        <form method="POST" action="manage_request.php" style="display:inline;">
                            <input type="hidden" name="request_id" value="123">
                            <button type="submit" name="action" value="decline">Отклонить</button>
                        </form>
                    </td>
                </tr>
                </tbody>
            </table>

            <h3>Ачивки для организаторов</h3>
            <div class="achievements-organizer">
                <span class="achievement-item">🏆 Организатор месяца</span>
                <span class="achievement-item">🥇 Самый активный вуз</span>
                <span class="achievement-item">🎖 100 участников</span>
            </div>

            <h3>Статистика мероприятий</h3>
            <table class="stats-table">
                <thead>
                <tr>
                    <th>Мероприятие</th>
                    <th>Посещаемость</th>
                    <th>Активность</th>
                    <th>Рейтинг</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>Мероприятие 1</td>
                    <td>150</td>
                    <td>75%</td>
                    <td>4.5</td>
                </tr>
                </tbody>
            </table>
        </section>
    <?php endif; ?>
</div>
</body>
</html>
