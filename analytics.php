<?php
// analytics.php
session_start();
require_once 'crypto.php';

// Доступ только для администратора
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.html");
    exit;
}

// Пути к файлам и папке для загрузок
$newsFile = 'data/news.json';
$eventsFile = 'data/events.json';
$imgUploadDir = 'data/img/';

// Загрузка новостей
$newsData = file_exists($newsFile) ? json_decode(file_get_contents($newsFile), true) : [];
if (!is_array($newsData)) { $newsData = []; }

// Загрузка мероприятий
$eventsData = file_exists($eventsFile) ? json_decode(file_get_contents($eventsFile), true) : [];
if (!is_array($eventsData)) { $eventsData = []; }

// Обработка POST-запросов
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // Добавление ачивки
    if ($action === 'add_achievement') {
        $targetEmail = trim($_POST['email'] ?? '');
        $achievement = trim($_POST['achievement'] ?? '');
        if ($targetEmail && $achievement) {
            $usersFile = 'users.json';
            if (file_exists($usersFile)) {
                $usersData = json_decode(file_get_contents($usersFile), true);
                $updated = false;
                foreach ($usersData as &$user) {
                    if ($user['email'] === $targetEmail) {
                        if (!isset($user['achievements']) || !is_array($user['achievements'])) {
                            $user['achievements'] = [];
                        }
                        $user['achievements'][] = $achievement;
                        $updated = true;
                        break;
                    }
                }
                if ($updated) {
                    file_put_contents($usersFile, json_encode($usersData, JSON_PRETTY_PRINT));
                }
            }
        }
        header("Location: analytics.php");
        exit;
    }
    
    // Добавление новости
    if ($action === 'add_news') {
        $title = trim($_POST['title'] ?? '');
        $date = trim($_POST['date'] ?? '');
        $text = trim($_POST['text'] ?? '');
        $imagePath = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $tmpName = $_FILES['image']['tmp_name'];
            $originalName = basename($_FILES['image']['name']);
            if (!is_dir($imgUploadDir)) {
                mkdir($imgUploadDir, 0777, true);
            }
            $targetPath = $imgUploadDir . time() . '_' . $originalName;
            if (move_uploaded_file($tmpName, $targetPath)) {
                $imagePath = $targetPath;
            }
        }
        if ($title && $date && $text) {
            $newId = count($newsData) ? max(array_column($newsData, 'id')) + 1 : 1;
            $newNews = [
                'id' => $newId,
                'title' => $title,
                'date' => $date,
                'image' => $imagePath,
                'text' => $text
            ];
            $newsData[] = $newNews;
            file_put_contents($newsFile, json_encode($newsData, JSON_PRETTY_PRINT));
        }
        header("Location: analytics.php");
        exit;
    }
    
    // Удаление новости
    if ($action === 'delete_news') {
        $newsId = intval($_POST['id'] ?? 0);
        $newsData = array_filter($newsData, function($item) use ($newsId) {
            return $item['id'] !== $newsId;
        });
        $newsData = array_values($newsData);
        file_put_contents($newsFile, json_encode($newsData, JSON_PRETTY_PRINT));
        header("Location: analytics.php");
        exit;
    }
    
    // Редактирование новости
    if ($action === 'edit_news') {
        $newsId = intval($_POST['id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $date = trim($_POST['date'] ?? '');
        $text = trim($_POST['text'] ?? '');
        foreach ($newsData as &$item) {
            if ($item['id'] === $newsId) {
                $item['title'] = $title;
                $item['date'] = $date;
                $item['text'] = $text;
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $tmpName = $_FILES['image']['tmp_name'];
                    $originalName = basename($_FILES['image']['name']);
                    if (!is_dir($imgUploadDir)) {
                        mkdir($imgUploadDir, 0777, true);
                    }
                    $targetPath = $imgUploadDir . time() . '_' . $originalName;
                    if (move_uploaded_file($tmpName, $targetPath)) {
                        $item['image'] = $targetPath;
                    }
                }
                break;
            }
        }
        file_put_contents($newsFile, json_encode($newsData, JSON_PRETTY_PRINT));
        header("Location: analytics.php");
        exit;
    }
    
    // Добавление мероприятия
    if ($action === 'add_event') {
        $date = trim($_POST['date'] ?? '');
        $title = trim($_POST['title'] ?? '');
        $format = trim($_POST['format'] ?? '');
        $place = trim($_POST['place'] ?? '');
        $tags = trim($_POST['tags'] ?? '');
        if ($date && $title) {
            $newEvent = [
                'date' => $date,
                'title' => $title,
                'format' => $format,
                'place' => $place,
                'tags' => $tags
            ];
            $eventsData[] = $newEvent;
            file_put_contents($eventsFile, json_encode($eventsData, JSON_PRETTY_PRINT));
        }
        header("Location: analytics.php");
        exit;
    }
    
    // Удаление мероприятия
    if ($action === 'delete_event') {
        $eventDate = $_POST['date'] ?? '';
        $eventTitle = $_POST['title'] ?? '';
        $eventsData = array_filter($eventsData, function($item) use ($eventDate, $eventTitle) {
            return !($item['date'] === $eventDate && $item['title'] === $eventTitle);
        });
        $eventsData = array_values($eventsData);
        file_put_contents($eventsFile, json_encode($eventsData, JSON_PRETTY_PRINT));
        header("Location: analytics.php");
        exit;
    }
    
    // Редактирование мероприятия
    if ($action === 'edit_event') {
        $originalDate = $_POST['original_date'] ?? '';
        $originalTitle = $_POST['original_title'] ?? '';
        $newDate = trim($_POST['date'] ?? '');
        $newTitle = trim($_POST['title'] ?? '');
        $format = trim($_POST['format'] ?? '');
        $place = trim($_POST['place'] ?? '');
        $tags = trim($_POST['tags'] ?? '');
        foreach ($eventsData as &$event) {
            if ($event['date'] === $originalDate && $event['title'] === $originalTitle) {
                $event['date'] = $newDate;
                $event['title'] = $newTitle;
                $event['format'] = $format;
                $event['place'] = $place;
                $event['tags'] = $tags;
                break;
            }
        }
        file_put_contents($eventsFile, json_encode($eventsData, JSON_PRETTY_PRINT));
        header("Location: analytics.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Аналитика и управление</title>
    <link rel="stylesheet" href="css/analytics.css">
</head>
<body>
    <div class="analytics-container">
        <header>
            <h1>Панель аналитики</h1>
        </header>

        
        <!-- Раздел: Управление новостями -->
        <section class="analytics-section">
            <h2>Управление новостями</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Заголовок</th>
                        <th>Дата</th>
                        <th>Изображение</th>
                        <th>Текст</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($newsData as $news): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($news['id'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($news['title'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($news['date'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td>
                            <?php if ($news['image']): ?>
                                <img src="<?php echo htmlspecialchars($news['image'], ENT_QUOTES, 'UTF-8'); ?>" alt="News Image" style="max-width:100px;">
                            <?php else: ?>
                                Нет
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($news['text'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td>
                            <!-- Форма для удаления новости -->
                            <form method="POST" action="analytics.php" style="display:inline-block;">
                                <input type="hidden" name="action" value="delete_news">
                                <input type="hidden" name="id" value="<?php echo htmlspecialchars($news['id'], ENT_QUOTES, 'UTF-8'); ?>">
                                <button type="submit">Удалить</button>
                            </form>
                            <!-- Форма для редактирования новости -->
                            <form method="POST" action="analytics.php" enctype="multipart/form-data" style="display:inline-block;">
                                <input type="hidden" name="action" value="edit_news">
                                <input type="hidden" name="id" value="<?php echo htmlspecialchars($news['id'], ENT_QUOTES, 'UTF-8'); ?>">
                                <input type="text" name="title" value="<?php echo htmlspecialchars($news['title'], ENT_QUOTES, 'UTF-8'); ?>" required>
                                <input type="date" name="date" value="<?php echo htmlspecialchars($news['date'], ENT_QUOTES, 'UTF-8'); ?>" required>
                                <input type="file" name="image">
                                <textarea name="text" required><?php echo htmlspecialchars($news['text'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                                <button type="submit">Редактировать</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <!-- Форма для добавления новости -->
            <h3>Добавить новость</h3>
            <form method="POST" action="analytics.php" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add_news">
                <label>Заголовок:</label>
                <input type="text" name="title" required>
                <label>Дата:</label>
                <input type="date" name="date" required>
                <label>Изображение:</label>
                <input type="file" name="image">
                <label>Текст новости:</label>
                <textarea name="text" required></textarea>
                <button type="submit">Добавить новость</button>
            </form>
        </section>
        
        <!-- Раздел: Управление мероприятиями -->
        <section class="analytics-section">
            <h2>Управление мероприятиями</h2>
            <table>
                <thead>
                    <tr>
                        <th>Дата</th>
                        <th>Заголовок</th>
                        <th>Формат</th>
                        <th>Место</th>
                        <th>Теги</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($eventsData as $event): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($event['date'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($event['title'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($event['format'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($event['place'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($event['tags'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td>
                            <!-- Форма для удаления мероприятия -->
                            <form method="POST" action="analytics.php" style="display:inline-block;">
                                <input type="hidden" name="action" value="delete_event">
                                <input type="hidden" name="date" value="<?php echo htmlspecialchars($event['date'], ENT_QUOTES, 'UTF-8'); ?>">
                                <input type="hidden" name="title" value="<?php echo htmlspecialchars($event['title'], ENT_QUOTES, 'UTF-8'); ?>">
                                <button type="submit">Удалить</button>
                            </form>
                            <!-- Форма для редактирования мероприятия -->
                            <form method="POST" action="analytics.php" style="display:inline-block;">
                                <input type="hidden" name="action" value="edit_event">
                                <input type="hidden" name="original_date" value="<?php echo htmlspecialchars($event['date'], ENT_QUOTES, 'UTF-8'); ?>">
                                <input type="hidden" name="original_title" value="<?php echo htmlspecialchars($event['title'], ENT_QUOTES, 'UTF-8'); ?>">
                                <input type="date" name="date" value="<?php echo htmlspecialchars($event['date'], ENT_QUOTES, 'UTF-8'); ?>" required>
                                <input type="text" name="title" value="<?php echo htmlspecialchars($event['title'], ENT_QUOTES, 'UTF-8'); ?>" required>
                                <input type="text" name="format" value="<?php echo htmlspecialchars($event['format'], ENT_QUOTES, 'UTF-8'); ?>">
                                <input type="text" name="place" value="<?php echo htmlspecialchars($event['place'], ENT_QUOTES, 'UTF-8'); ?>">
                                <input type="text" name="tags" value="<?php echo htmlspecialchars($event['tags'], ENT_QUOTES, 'UTF-8'); ?>">
                                <button type="submit">Редактировать</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <!-- Форма для добавления мероприятия -->
            <h3>Добавить мероприятие</h3>
            <form method="POST" action="analytics.php">
                <input type="hidden" name="action" value="add_event">
                <label>Дата:</label>
                <input type="date" name="date" required>
                <label>Заголовок:</label>
                <input type="text" name="title" required>
                <label>Формат:</label>
                <input type="text" name="format">
                <label>Место:</label>
                <input type="text" name="place">
                <label>Теги:</label>
                <input type="text" name="tags">
                <button type="submit">Добавить мероприятие</button>
            </form>
        </section>
        
        <footer>
            <a href="profile.php" class="back-button">Вернуться в профиль</a>
        </footer>
    </div>
</body>
</html>
