<?php
session_start();

// Обработка выход
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header('Location: index.php');
    exit;
}

if (!isset($_SESSION['user_id'])) {
    die('Чтобы оставить заявку, надо <a href="login.php">войти в аккаунт</a>.');
}

$success = false;
$error = false;
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $review = $_POST['review'];
    $date = $_POST['date'];
    $curses = $_POST['curses'];
    $payment = $_POST['payment'];
    $status = 'Новая';
    
    include('db.php');
    
    $user_id = (int)$_SESSION['user_id'];
    $review = $con->real_escape_string($review);
    $curses = $con->real_escape_string($curses);
    $payment = $con->real_escape_string($payment);
    
    $query = $con->query("INSERT INTO request (review, date, curses, payment, user_id, status) 
                          VALUES ('$review', '$date', '$curses', '$payment', '$user_id', '$status')");
    
    if (!$query) {
        $error = true;
        $error_msg = 'Ошибка: ' . $con->error;
    } else {
        $success = true;
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Новая заявка - Учусь.РФ</title>
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
            background: rgba(255,255,255,0.15);
            color: #ffffff;
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
            align-items: center;
            padding: 40px 20px;
        }

        .container {
            max-width: 650px;
            width: 100%;
            background: #ffffff;
            padding: 40px;
            border-radius: 28px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            animation: fadeInUp 0.5s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        h1 {
            font-size: 28px;
            font-weight: 700;
            color: #0d47a1;
            text-align: center;
            margin-bottom: 25px;
        }

        /* Сообщения */
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 20px;
            border-radius: 24px;
            margin-bottom: 25px;
            text-align: center;
            border-left: 4px solid #28a745;
        }

        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 20px;
            border-radius: 24px;
            margin-bottom: 25px;
            text-align: center;
            border-left: 4px solid #dc3545;
        }

        .success-message a, .error-message a {
            color: inherit;
            font-weight: 600;
            text-decoration: underline;
        }

        /* Форма */
        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #495057;
        }

        label i {
            margin-right: 8px;
            color: #007bff;
        }

        input, select, textarea {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #ced4da;
            border-radius: 24px;
            font-size: 15px;
            font-family: 'Inter', sans-serif;
            transition: 0.2s;
            background: #fff;
        }

        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0,123,255,0.2);
        }

        textarea {
            resize: vertical;
            min-height: 100px;
        }

        .btn-submit {
            width: 100%;
            padding: 14px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 40px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 10px;
        }

        .btn-submit:hover {
            background: #0d47a1;
            transform: translateY(-2px);
            box-shadow: 0 6px 14px rgba(0,123,255,0.3);
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
            color: #ffffff;
        }

        .footer-col p, .footer-col a {
            font-size: 14px;
            color: #dee2e6;
            text-decoration: none;
        }

        .footer-col a:hover {
            text-decoration: underline;
        }

        .footer-bottom {
            text-align: center;
            padding-top: 30px;
            margin-top: 30px;
            border-top: 1px solid rgba(222,226,230,0.3);
            font-size: 13px;
        }

        /* Адаптивность */
        @media (max-width: 768px) {
            .nav {
                flex-direction: column;
                text-align: center;
            }
            .logo {
                font-size: 24px;
            }
            .nav-buttons a {
                padding: 6px 14px;
                font-size: 14px;
            }
            .container {
                padding: 30px 20px;
            }
            h1 {
                font-size: 24px;
            }
            .footer-content {
                grid-template-columns: 1fr;
                text-align: center;
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
            <a href="history.php">Мои заявки</a>
            <a href="?logout=1">Выход</a>
        </div>
    </div>
</header>

<div class="main-wrapper">
    <div class="container">
        <h1><i class="fas fa-pen-alt"></i> Новая заявка</h1>

        <?php if ($success): ?>
            <div class="success-message">
                <i class="fas fa-check-circle"></i> Заявка успешно отправлена!<br><br>
                <a href="history.php">Перейти к истории моих заявок →</a>
            </div>
        <?php elseif ($error): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-triangle"></i> Ошибка: <?php echo htmlspecialchars($error_msg); ?><br>
                <a href="javascript:history.back()">Попробовать снова</a>
            </div>
        <?php endif; ?>

        <?php if (!$success): ?>
        <form method="POST" id="requestForm">
            <div class="form-group">
                <label for="curses"><i class="fas fa-graduation-cap"></i> Название курса</label>
                <select id="curses" name="curses" required>
                    <option value="Курсы повышения квалификации">Курсы повышения квалификации</option>
                    <option value="Курсы переподготовки">Курсы переподготовки</option>
                    <option value="Курсы по охране труда">Курсы по охране труда</option>
                    <option value="Курсы для педагогов">Курсы для педагогов</option>
                </select>
            </div>

            <div class="form-group">
                <label for="date"><i class="fas fa-calendar-alt"></i> Когда желаете начать обучение?</label>
                <input type="datetime-local" id="date" name="date" required>
            </div>

            <div class="form-group">
                <label for="payment"><i class="fas fa-credit-card"></i> Способ оплаты</label>
                <select id="payment" name="payment" required>
                    <option value="наличные">Наличные</option>
                    <option value="перевод">Переводом по номеру</option>
                    <option value="карта">Банковской картой</option>
                </select>
            </div>

            <div class="form-group">
                <label for="review"><i class="fas fa-comment"></i> Дополнительная информация</label>
                <textarea id="review" name="review" placeholder="Опишите ваши пожелания или комментарий..."></textarea>
            </div>

            <button type="submit" class="btn-submit" id="submitBtn">
                <i class="fas fa-paper-plane"></i> Отправить заявку
            </button>
        </form>
        <?php endif; ?>
    </div>
</div>

<footer class="footer">
    <div class="footer-content">
        <div class="footer-col">
            <h4>Учусь.РФ</h4>
            <p>Дополнительное образование для взрослых и детей.</p>
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
        &copy; 2025 Учусь.РФ — все права защищены.
    </div>
</footer>

<script>
    const form = document.getElementById('requestForm');
    const submitBtn = document.getElementById('submitBtn');
    if (form) {
        form.addEventListener('submit', function() {
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Отправка...';
            submitBtn.disabled = true;
        });
    }
</script>
</body>
</html>