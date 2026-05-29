<?php
include('db.php');
session_start();

// Проверка авторизации админ
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header('Location: login.php');
    exit;
}

// Выход
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header('Location: index.php');
    exit;
}

// Допустимые статусы
$valid_statuses = ['Новая', 'Идет обучение', 'Обучение завершено'];

// Обработка изменения статуса
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['request_id'])) {
    $request_id = (int)$_POST['request_id'];
    $status = $_POST['status'] ?? '';
    if (!in_array($status, $valid_statuses, true)) die('Недопустимый статус');
    $stmt = $con->prepare("UPDATE request SET status = ? WHERE id = ?");
    $stmt->bind_param('si', $status, $request_id);
    if (!$stmt->execute()) die('Ошибка обновления: ' . $con->error);
    $params = $_GET;
    unset($params['updated']);
    $query_string = http_build_query($params);
    header("Location: ?" . ($query_string ? $query_string . '&updated=1' : 'updated=1'));
    exit;
}

// Фильтр и сортировка
$status_filter = isset($_GET['status_filter']) && in_array($_GET['status_filter'], $valid_statuses, true) ? $_GET['status_filter'] : '';
$sort_field = $_GET['sort_field'] ?? 'date';
$sort_order = $_GET['sort_order'] ?? 'DESC';
$allowed_sort_fields = ['date', 'users.login', 'users.fullname', 'request.id'];
if (!in_array($sort_field, $allowed_sort_fields, true)) $sort_field = 'date';
$sort_order = strtoupper($sort_order);
if (!in_array($sort_order, ['ASC', 'DESC'], true)) $sort_order = 'DESC';

// Пагинация
$page = (int)($_GET['page'] ?? 1);
$limit = 10;
$offset = ($page - 1) * $limit;

$where = '';
$params = [];
$types = '';
if ($status_filter !== '') {
    $where = "WHERE request.status = ?";
    $params[] = $status_filter;
    $types .= 's';
}

$sql = "
    SELECT request.*, users.login, users.fullname,
           COUNT(*) OVER() as total_count
    FROM request
    INNER JOIN users ON request.user_id = users.id
    $where
    ORDER BY $sort_field $sort_order
    LIMIT ? OFFSET ?
";
$params[] = $limit;
$params[] = $offset;
$types .= 'ii';

$stmt = $con->prepare($sql);
if (!$stmt) die('Ошибка подготовки');
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$query = $stmt->get_result();
if (!$query) die('Ошибка запроса');

$total_rows = 0;
if ($query->num_rows > 0) {
    $first_row = $query->fetch_assoc();
    $total_rows = (int)$first_row['total_count'];
    $query->data_seek(0);
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель администратора — Учусь.РФ</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #ced4da;
            color: #212529;
            line-height: 1.5;
        }

        .header {
            background: #007bff;
            padding: 16px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .nav {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .logo {
            font-size: 28px;
            font-weight: 700;
            color: #ffffff;
            text-decoration: none;
        }

        .nav-buttons {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .nav-buttons a {
            padding: 8px 20px;
            border-radius: 40px;
            font-weight: 500;
            font-size: 16px;
            text-decoration: none;
            transition: all 0.2s;
            background: rgba(255,255,255,0.15);
            color: #ffffff;
            border: 1px solid rgba(255,255,255,0.3);
        }

        .nav-buttons a:hover {
            background: #0d47a1;
            transform: translateY(-2px);
        }

        .admin-container {
            max-width: 1200px;
            margin: 40px auto;
            background: #ffffff;
            border-radius: 28px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            overflow: hidden;
        }

        .admin-header {
            padding: 30px 30px 20px;
            border-bottom: 1px solid #dee2e6;
        }

        .admin-header h1 {
            font-size: 28px;
            font-weight: 700;
            color: #0d47a1;
            margin-bottom: 6px;
        }

        .admin-header p {
            font-size: 16px;
            color: #495057;
        }

        .filter-section {
            padding: 20px 30px;
            background: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }

        .filter-form {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            align-items: flex-end;
        }

        .filter-group {
            flex: 1;
            min-width: 160px;
        }

        .filter-group:last-child {
            display: flex;
            gap: 10px;
            flex: 0 0 auto;
        }

        .filter-group label {
            display: block;
            font-size: 12px;
            font-weight: 500;
            margin-bottom: 6px;
            color: #495057;
        }

        .filter-group select, .filter-group input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ced4da;
            border-radius: 16px;
            font-family: 'Inter', sans-serif;
            font-size: 14px;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 40px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            border: none;
            transition: 0.2s;
        }

        .btn-primary {
            background: #007bff;
            color: white;
        }

        .btn-primary:hover {
            background: #0d47a1;
            transform: translateY(-2px);
        }

        .btn-outline-light {
            background: transparent;
            border: 1px solid #ced4da;
            color: #212529;
        }

        .btn-outline-light:hover {
            background: #dee2e6;
        }

        /* Карточки заявок — единая синяя левая граница, белый фон */
        .requests-container {
            padding: 30px;
        }

        .request-item {
            background: #ffffff;
            border-radius: 24px;
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            border-left: 4px solid #007bff;
            transition: 0.2s;
        }

        .request-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.08);
        }

        .request-header {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 20px;
        }

        .user-info h3 {
            font-size: 18px;
            font-weight: 600;
            color: #0d47a1;
        }

        .user-info p {
            font-size: 14px;
            color: #495057;
        }

        .request-id {
            background: #dee2e6;
            padding: 4px 12px;
            border-radius: 30px;
            font-size: 13px;
            font-weight: 500;
        }

        /* Единый нейтральный бейдж статуса */
        .status-badge {
            padding: 4px 12px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            background: #e9ecef;
            color: #495057;
        }

        .request-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 12px;
            margin: 20px 0;
        }

        .detail-item {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 16px;
        }

        .detail-label {
            font-size: 11px;
            font-weight: 500;
            color: #6c757d;
            text-transform: uppercase;
        }

        .detail-value {
            font-size: 15px;
            font-weight: 500;
            color: #212529;
            margin-top: 4px;
        }

        .status-form {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
        }

        .form-select {
            width: 100%;
            padding: 10px;
            border-radius: 20px;
            border: 1px solid #ced4da;
            font-family: 'Inter', sans-serif;
            margin-top: 8px;
        }

        .btn-save {
            width: 100%;
            margin-top: 12px;
            background: #007bff;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 40px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s;
        }

        .btn-save:hover {
            background: #0d47a1;
            transform: translateY(-2px);
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin: 30px 0;
        }

        .page-link {
            padding: 8px 14px;
            border-radius: 30px;
            background: #ffffff;
            border: 1px solid #dee2e6;
            text-decoration: none;
            color: #007bff;
            font-weight: 500;
        }

        .page-link.active, .page-link:hover {
            background: #007bff;
            color: white;
            border-color: #007bff;
        }

        .empty-state {
            text-align: center;
            padding: 60px;
            background: #f8f9fa;
            border-radius: 24px;
        }

        /* Футер (как на главной, без лицензии) */
        .footer {
            background: #0d47a1;
            color: #dee2e6;
            padding: 40px 20px 20px;
            margin-top: 40px;
        }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 30px;
        }

        .footer-col h4 {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 16px;
            color: #ffffff;
        }

        .footer-col p, .footer-col a {
            font-size: 14px;
            color: #dee2e6;
            text-decoration: none;
        }

        .footer-bottom {
            text-align: center;
            padding-top: 30px;
            margin-top: 30px;
            border-top: 1px solid rgba(222,226,230,0.3);
            font-size: 13px;
        }

        @media (max-width: 768px) {
            .nav { flex-direction: column; text-align: center; }
            .admin-container { margin: 20px; }
            .filter-group:last-child { flex-direction: row; justify-content: space-between; }
            .footer-content { grid-template-columns: 1fr; text-align: center; }
        }
    </style>
</head>
<body>

<header class="header">
    <div class="nav">
        <a href="index.php" class="logo">Учусь.РФ</a>
        <div class="nav-buttons">
            <a href="index.php"><i class="fas fa-home"></i> Главная</a>
            <a href="?logout=1" onclick="return confirm('Выйти из аккаунта?')"><i class="fas fa-sign-out-alt"></i> Выход</a>
        </div>
    </div>
</header>

<div class="admin-container">
    <div class="admin-header">
        <h1>Панель администратора</h1>
        <p>Управление заявками пользователей</p>
    </div>

    <div class="filter-section">
        <form method="GET" class="filter-form">
            <div class="filter-group">
                <label>Фильтр по статусу</label>
                <select name="status_filter">
                    <option value="">Все статусы</option>
                    <option value="Новая" <?= $status_filter === 'Новая' ? 'selected' : '' ?>>Новая</option>
                    <option value="Идет обучение" <?= $status_filter === 'Идет обучение' ? 'selected' : '' ?>>Идет обучение</option>
                    <option value="Обучение завершено" <?= $status_filter === 'Обучение завершено' ? 'selected' : '' ?>>Обучение завершено</option>
                </select>
            </div>
            <div class="filter-group">
                <label>Сортировать по</label>
                <select name="sort_field">
                    <option value="date" <?= $sort_field === 'date' ? 'selected' : '' ?>>Дата подачи</option>
                    <option value="users.login" <?= $sort_field === 'users.login' ? 'selected' : '' ?>>Логин</option>
                    <option value="users.fullname" <?= $sort_field === 'users.fullname' ? 'selected' : '' ?>>ФИО</option>
                    <option value="request.id" <?= $sort_field === 'request.id' ? 'selected' : '' ?>>Номер заявки</option>
                </select>
            </div>
            <div class="filter-group">
                <label>Порядок</label>
                <select name="sort_order">
                    <option value="DESC" <?= $sort_order === 'DESC' ? 'selected' : '' ?>>По убыванию</option>
                    <option value="ASC" <?= $sort_order === 'ASC' ? 'selected' : '' ?>>По возрастанию</option>
                </select>
            </div>
            <div class="filter-group">
                <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Применить</button>
                <a href="?" class="btn btn-outline-light"><i class="fas fa-undo-alt"></i> Сбросить</a>
            </div>
        </form>
    </div>

    <div class="requests-container">
        <?php if ($query->num_rows === 0): ?>
            <div class="empty-state">
                <i class="fas fa-inbox" style="font-size: 48px; color: #adb5bd;"></i>
                <h3 style="margin-top: 16px;">Заявок не найдено</h3>
                <p>Измените параметры фильтрации</p>
            </div>
        <?php else: ?>
            <?php while ($request = $query->fetch_assoc()): ?>
                <div class="request-item">
                    <div class="request-header">
                        <div>
                            <h3><?= htmlspecialchars($request['login']) ?></h3>
                            <p><?= htmlspecialchars($request['fullname']) ?></p>
                        </div>
                        <div style="display: flex; gap: 12px; align-items: center;">
                            <span class="request-id">№ <?= $request['id'] ?></span>
                            <span class="status-badge"><?= $request['status'] ?></span>
                        </div>
                    </div>

                    <div class="request-details">
                        <div class="detail-item"><div class="detail-label">Дата</div><div class="detail-value"><?= $request['date'] ?></div></div>
                        <div class="detail-item"><div class="detail-label">Услуга</div><div class="detail-value"><?= htmlspecialchars($request['curses'] ?? '—') ?></div></div>
                        <div class="detail-item"><div class="detail-label">Оплата</div><div class="detail-value"><?= htmlspecialchars($request['payment'] ?? '—') ?></div></div>
                        <div class="detail-item"><div class="detail-label">Комментарий</div><div class="detail-value"><?= nl2br(htmlspecialchars($request['review'] ?? '—')) ?></div></div>
                    </div>

                    <div class="status-form">
                        <form method="POST">
                            <input type="hidden" name="request_id" value="<?= $request['id'] ?>">
                            <label>Изменить статус:</label>
                            <select name="status" class="form-select">
                                <option value="Новая" <?= $request['status'] == 'Новая' ? 'selected' : '' ?>>🆕 Новая</option>
                                <option value="Идет обучение" <?= $request['status'] == 'Идет обучение' ? 'selected' : '' ?>>📖 Идёт обучение</option>
                                <option value="Обучение завершено" <?= $request['status'] == 'Обучение завершено' ? 'selected' : '' ?>>✅ Обучение завершено</option>
                            </select>
                            <button type="submit" class="btn-save"><i class="fas fa-save"></i> Сохранить</button>
                        </form>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>

        <?php if ($total_rows > $limit): ?>
            <div class="pagination">
                <?php
                $total_pages = ceil($total_rows / $limit);
                $query_params = array_filter($_GET, fn($key) => $key !== 'page', ARRAY_FILTER_USE_KEY);
                for ($i = 1; $i <= $total_pages; $i++):
                    $query_params['page'] = $i;
                    $url = '?' . http_build_query($query_params);
                ?>
                    <a href="<?= $url ?>" class="page-link <?= $page === $i ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<footer class="footer">
    <div class="footer-content">
        <div class="footer-col">
            <h4>Учусь.РФ</h4>
            <p>Дополнительное образование для взрослых и детей</p>
        </div>
        <div class="footer-col">
            <h4>Контакты</h4>
            <p><i class="fas fa-phone-alt"></i> +7 (800) 555-35-35</p>
            <p><i class="fas fa-envelope"></i> info@uchus.ru</p>
        </div>
        <div class="footer-col">
            <h4>Режим работы</h4>
            <p>Пн–Пт: 9:00 – 20:00<br>Сб: 10:00 – 16:00</p>
        </div>
    </div>
    <div class="footer-bottom">
        &copy; 2026 Учусь.РФ — все права защищены.
    </div>
</footer>

<script>
    if (window.location.search.includes('updated')) {
        const notif = document.createElement('div');
        notif.innerHTML = '✅ Статус заявки обновлён';
        notif.style.cssText = 'position:fixed;top:20px;right:20px;background:#28a745;color:white;padding:12px 24px;border-radius:40px;font-weight:500;z-index:1000;box-shadow:0 4px 12px rgba(0,0,0,0.15);';
        document.body.appendChild(notif);
        setTimeout(() => notif.remove(), 3000);
    }
</script>
</body>
</html>