<?php
session_start();

if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header('Location: index.php');
    exit;
}

if (!isset($_SESSION['user_id'])) {
    die('Чтобы посмотреть историю заявок, надо <a href="login.php">войти в аккаунт</a>.');
}
include('db.php');

// Обработка отзыва — сохраняется в таблицу users
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['review'])) {
    $review = $con->real_escape_string($_POST['review']);
    $user_id = (int)$_SESSION['user_id'];
    $con->query("UPDATE users SET review = '$review' WHERE id = '$user_id'");
    $message = '<div class="success-message"><i class="fas fa-check-circle"></i> Отзыв успешно сохранён!</div>';
}

$user_id = (int)$_SESSION['user_id'];
$query = $con->query("SELECT * FROM request WHERE user_id = '$user_id' ORDER BY date DESC");
if (!$query) die('Ошибка запроса: ' . $con->error);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Мои заявки - Учусь.РФ</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #ced4da;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Шапка */
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
            color: #fff;
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
            background: rgba(255,255,255,0.15);
            color: #fff;
            border: 1px solid rgba(255,255,255,0.3);
            transition: 0.2s;
        }

        .nav-buttons a:hover {
            background: #0d47a1;
            transform: translateY(-2px);
        }

        /* Основной контейнер */
        .main-wrapper {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 40px 20px;
        }

        .container {
            max-width: 900px;
            width: 100%;
            background: #fff;
            padding: 40px;
            border-radius: 28px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            animation: fadeInUp 0.5s ease-out;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        h1 {
            font-size: 28px;
            font-weight: 700;
            color: #0d47a1;
            text-align: center;
            margin-bottom: 30px;
        }

        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 12px 20px;
            border-radius: 40px;
            margin-bottom: 25px;
            text-align: center;
        }

        /* Карточки заявок */
        .request {
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: 24px;
            padding: 24px;
            margin-bottom: 24px;
            transition: 0.2s;
        }

        .request:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }

        .request-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px dashed #dee2e6;
        }

        .request-number {
            font-size: 20px;
            font-weight: 700;
            color: #007bff;
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .status-new { background: #fff3cd; color: #856404; }
        .status-progress { background: #d1ecf1; color: #0c5460; }
        .status-completed { background: #d4edda; color: #155724; }

        .request-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 12px;
            margin: 15px 0;
        }

        .detail-item {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 16px;
            word-break: break-word;
        }

        .detail-label {
            font-size: 12px;
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

        /* Форма отзыва */
        .review-form {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            align-items: center;
        }

        .review-form input {
            flex: 1;
            padding: 10px 16px;
            border: 1px solid #ced4da;
            border-radius: 40px;
            font-family: 'Inter', sans-serif;
            min-width: 150px;
        }

        .review-form input:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0,123,255,0.2);
        }

        .review-form button {
            background: #28a745;
            color: white;
            border: none;
            padding: 10px 24px;
            border-radius: 40px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s;
        }

        .review-form button:hover {
            background: #1e7e34;
            transform: translateY(-2px);
        }

        .empty-state {
            text-align: center;
            padding: 60px;
            background: #f8f9fa;
            border-radius: 24px;
            color: #6c757d;
        }

        /* Футер */
        .footer {
            background: #0d47a1;
            color: #dee2e6;
            padding: 40px 20px 20px;
            margin-top: auto;
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
            color: #fff;
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

        /* ===== АДАПТИВНОСТЬ ===== */
        @media (max-width: 768px) {
            .nav {
                flex-direction: column;
                text-align: center;
                gap: 12px;
            }
            .logo {
                font-size: 24px;
            }
            .nav-buttons a {
                padding: 6px 14px;
                font-size: 14px;
            }
            .container {
                padding: 24px 16px;
            }
            h1 {
                font-size: 24px;
                margin-bottom: 20px;
            }
            .request {
                padding: 18px;
            }
            .request-header {
                flex-direction: column;
                align-items: flex-start;
            }
            .status-badge {
                white-space: normal;
                text-align: center;
                width: auto;
            }
            .request-details {
                grid-template-columns: 1fr;
                gap: 10px;
            }
            .review-form {
                flex-direction: column;
                align-items: stretch;
                gap: 10px;
            }
            .review-form input {
                width: 100%;
            }
            .review-form button {
                width: 100%;
            }
            .empty-state {
                padding: 40px 20px;
            }
            .footer-content {
                grid-template-columns: 1fr;
                text-align: center;
                gap: 24px;
            }
        }

        @media (max-width: 480px) {
            .main-wrapper {
                padding: 20px 12px;
            }
            .container {
                padding: 20px 12px;
            }
            h1 {
                font-size: 20px;
            }
            .request-number {
                font-size: 18px;
            }
            .detail-item {
                padding: 8px;
            }
            .detail-value {
                font-size: 14px;
            }
            .footer {
                padding: 30px 16px 16px;
            }
        }
    </style>
</head>
<body>

<header class="header">
    <div class="nav">
        <a href="index.php" class="logo">Учусь.РФ</a>
        <div class="nav-buttons">
            <a href="index.php">Главная</a>
            <a href="create.php">Новая заявка</a>
            <a href="?logout=1">Выход</a>
        </div>
    </div>
</header>

<div class="main-wrapper">
    <div class="container">
        <h1><i class="fas fa-history"></i> История заявок</h1>

        <?php if (isset($message)) echo $message; ?>

        <?php if ($query->num_rows == 0): ?>
            <div class="empty-state">
                <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 16px; display: block;"></i>
                <p>У вас пока нет заявок.</p>
                <a href="create.php" style="display: inline-block; margin-top: 16px; color: #007bff;">Создать первую заявку →</a>
            </div>
        <?php else: ?>
            <?php $counter = 1; while ($request = $query->fetch_assoc()): 
                $status_class = match($request['status']) {
                    'Новая' => 'status-new',
                    'Идет обучение' => 'status-progress',
                    'Обучение завершено' => 'status-completed',
                    default => 'status-new'
                };
            ?>
                <div class="request">
                    <div class="request-header">
                        <span class="request-number">Заявка №<?php echo $counter; ?></span>
                        <span class="status-badge <?php echo $status_class; ?>"><?php echo htmlspecialchars($request['status']); ?></span>
                    </div>
                    <div class="request-details">
                        <div class="detail-item"><div class="detail-label">Дата подачи</div><div class="detail-value"><?php echo htmlspecialchars($request['date']); ?></div></div>
                        <div class="detail-item"><div class="detail-label">Услуга</div><div class="detail-value"><?php echo htmlspecialchars($request['curses']); ?></div></div>
                        <div class="detail-item"><div class="detail-label">Оплата</div><div class="detail-value"><?php echo htmlspecialchars($request['payment']); ?></div></div>
                    </div>

                    <?php if ($request['status'] === 'Обучение завершено'): ?>
                        <div class="review-form">
                            <form method="POST">
                                <input type="text" name="review" placeholder="Оставьте отзыв об обучении...">
                                <button type="submit"><i class="fas fa-comment"></i> Отправить отзыв</button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            <?php $counter++; endwhile; ?>
        <?php endif; ?>
    </div>
</div>

<footer class="footer">
    <div class="footer-content">
        <div class="footer-col"><h4>Учусь.РФ</h4><p>Дополнительное образование для взрослых и детей.</p></div>
        <div class="footer-col"><h4>Контакты</h4><p><i class="fas fa-phone-alt"></i> +7 (800) 555-35-35</p><p><i class="fas fa-envelope"></i> info@uchus.ru</p></div>
        <div class="footer-col"><h4>Режим работы</h4><p>Пн–Пт: 9:00 – 20:00<br>Сб: 10:00 – 16:00</p></div>
    </div>
    <div class="footer-bottom">&copy; 2025 Учусь.РФ — все права защищены.</div>
</footer>

</body>
</html>