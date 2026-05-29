<?php
session_start();

if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['admin']) && $_SESSION['admin']) {
        header('Location: admin.php');
    } else {
        header('Location: create.php');
    }
    exit;
}

$error = '';
$success = false;
$form_data = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login = trim($_POST['login']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];
    $fullname = trim($_POST['fullname']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    
    $form_data = compact('login', 'fullname', 'phone', 'email');
    
    if (empty($login) || empty($password) || empty($fullname) || empty($phone) || empty($email)) {
        $error = 'Пожалуйста, заполните все поля';
    } elseif ($password !== $confirm) {
        $error = 'Пароли не совпадают';
    } elseif (strlen($password) < 8) {
        $error = 'Пароль должен содержать минимум 8 символов';
    } elseif (!preg_match('/^\+7\(\d{3}\)\d{3}-\d{2}-\d{2}$/', $phone)) {
        $error = 'Телефон должен быть в формате +7(XXX)XXX-XX-XX';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Введите корректный email';
    } else {
        include('db.php');
        $stmt = $con->prepare("SELECT id FROM users WHERE login = ?");
        $stmt->bind_param("s", $login);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $error = 'Пользователь с таким логином уже существует';
        } else {
            $stmt = $con->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                $error = 'Пользователь с таким email уже существует';
            } else {
                $stmt = $con->prepare("INSERT INTO users (login, password, fullname, phone, email) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sssss", $login, $password, $fullname, $phone, $email);
                if ($stmt->execute()) {
                    $success = true;
                    header('refresh:2;url=login.php');
                } else {
                    $error = 'Ошибка при регистрации. Попробуйте позже.';
                }
            }
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация - Учусь.РФ</title>
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

        /* ШАПКА */
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

        /* ОСНОВНОЙ КОНТЕЙНЕР */
        .main-wrapper {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px 20px;
        }

        .container {
            max-width: 550px;
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

        .logo-form {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo-form h1 {
            font-size: 32px;
            background: linear-gradient(135deg, #007bff, #0d47a1);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .logo-form p {
            color: #6c757d;
            font-size: 14px;
            margin-top: 5px;
        }

        .form-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .form-header h2 {
            font-size: 28px;
            font-weight: 700;
            color: #0d47a1;
            margin-bottom: 8px;
        }

        .form-header p {
            color: #6c757d;
            font-size: 14px;
        }

        /* СООБЩЕНИЯ */
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 12px 20px;
            border-radius: 40px;
            margin-bottom: 25px;
            text-align: center;
            border-left: 3px solid #dc3545;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-size: 14px;
        }

        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 20px;
            border-radius: 24px;
            margin-bottom: 25px;
            text-align: center;
            border-left: 3px solid #28a745;
        }

        /* ФОРМА */
        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #495057;
        }

        .form-group label i {
            margin-right: 8px;
            color: #007bff;
        }

        .form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #ced4da;
            border-radius: 24px;
            font-size: 15px;
            font-family: 'Inter', sans-serif;
            transition: 0.2s;
            background: #fff;
        }

        .form-group input:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0,123,255,0.2);
        }

        .hint {
            font-size: 12px;
            color: #6c757d;
            margin-top: 5px;
            display: block;
        }

        .btn-register {
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

        .btn-register:hover {
            background: #0d47a1;
            transform: translateY(-2px);
            box-shadow: 0 6px 14px rgba(0,123,255,0.3);
        }

        .form-footer {
            margin-top: 25px;
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
        }

        .form-footer a {
            color: #007bff;
            text-decoration: none;
            font-weight: 600;
        }

        /* ФУТЕР */
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

        .footer-bottom {
            text-align: center;
            padding-top: 30px;
            margin-top: 30px;
            border-top: 1px solid rgba(222,226,230,0.3);
            font-size: 13px;
        }

        /* АДАПТИВНОСТЬ */
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
            <a href="login.php">Войти</a>
        </div>
    </div>
</header>

<div class="main-wrapper">
    <div class="container">
        <div class="logo-form">
            <h1>Учусь.РФ</h1>
            <p>Курсы повышения квалификации</p>
        </div>

        <div class="form-header">
            <h2>Создание аккаунта</h2>
            <p>Заполните форму для регистрации</p>
        </div>

        <?php if ($error): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success-message">
                <i class="fas fa-check-circle"></i> Регистрация успешно завершена!<br>
                <small>Перенаправление на страницу входа...</small>
            </div>
        <?php endif; ?>

        <?php if (!$success): ?>
        <form method="POST" id="registerForm">
            <div class="form-group">
                <label for="fullname"><i class="fas fa-user"></i> ФИО</label>
                <input type="text" id="fullname" name="fullname" 
                       value="<?php echo htmlspecialchars($form_data['fullname'] ?? ''); ?>"
                       placeholder="Иванов Иван Иванович" required>
                <span class="hint">Ваше полное имя</span>
            </div>

            <div class="form-group">
                <label for="phone"><i class="fas fa-phone-alt"></i> Телефон</label>
                <input type="tel" id="phone" name="phone" 
                       value="<?php echo htmlspecialchars($form_data['phone'] ?? ''); ?>"
                       placeholder="+7(XXX)XXX-XX-XX" required>
                <span class="hint">Формат: +7(XXX)XXX-XX-XX</span>
            </div>

            <div class="form-group">
                <label for="email"><i class="fas fa-envelope"></i> Email</label>
                <input type="email" id="email" name="email" 
                       value="<?php echo htmlspecialchars($form_data['email'] ?? ''); ?>"
                       placeholder="example@mail.com" required>
                <span class="hint">На этот адрес будут приходить уведомления</span>
            </div>

            <div class="form-group">
                <label for="login"><i class="fas fa-key"></i> Логин</label>
                <input type="text" id="login" name="login" 
                       value="<?php echo htmlspecialchars($form_data['login'] ?? ''); ?>"
                       placeholder="ivan123" required>
                <span class="hint">Только латиница и цифры, минимум 6 символов</span>
            </div>

            <div class="form-group">
                <label for="password"><i class="fas fa-lock"></i> Пароль</label>
                <input type="password" id="password" name="password" 
                       placeholder="Минимум 8 символов" required>
                <span class="hint" id="passwordHint">Минимум 8 символов</span>
            </div>

            <div class="form-group">
                <label for="confirm_password"><i class="fas fa-check-circle"></i> Подтверждение пароля</label>
                <input type="password" id="confirm_password" name="confirm_password" 
                       placeholder="Повторите пароль" required>
                <span class="hint" id="confirmHint"></span>
            </div>

            <button type="submit" class="btn-register" id="submitBtn">
                <i class="fas fa-user-plus"></i> Зарегистрироваться
            </button>
        </form>
        <?php endif; ?>

        <div class="form-footer">
            <p>Уже есть аккаунт? <a href="login.php">Войти →</a></p>
            <a href="index.php" style="display:inline-block; margin-top:12px; color:#6c757d; text-decoration:none; font-size:14px;">← Вернуться на главную</a>
        </div>
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

    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');
    const passwordHint = document.getElementById('passwordHint');
    const confirmHint = document.getElementById('confirmHint');

    if (password) {
        password.addEventListener('input', function() {
            if (this.value.length >= 8) {
                passwordHint.innerHTML = '✅ Пароль надёжный';
                passwordHint.style.color = '#28a745';
            } else {
                passwordHint.innerHTML = '⚠️ Минимум 8 символов';
                passwordHint.style.color = '#dc3545';
            }
            if (confirmPassword.value) checkMatch();
        });
    }

    function checkMatch() {
        if (password.value === confirmPassword.value && password.value.length >= 8) {
            confirmHint.innerHTML = '✅ Пароли совпадают';
            confirmHint.style.color = '#28a745';
        } else if (confirmPassword.value.length > 0) {
            confirmHint.innerHTML = '❌ Пароли не совпадают';
            confirmHint.style.color = '#dc3545';
        } else {
            confirmHint.innerHTML = '';
        }
    }

    if (confirmPassword) {
        confirmPassword.addEventListener('input', checkMatch);
    }

    // Автоматическое форматирование телефон
    const phone = document.getElementById('phone');
    if (phone) {
        phone.addEventListener('input', function() {
            let value = this.value;
            if (value.length === 1 && value !== '+') {
                this.value = '+' + value;
            }
        });
    }

    // Валидация перед отправкой
    const form = document.getElementById('registerForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            if (password.value !== confirmPassword.value) {
                e.preventDefault();
                alert('Пароли не совпадают');
                confirmPassword.style.borderColor = '#dc3545';
                return false;
            }
            if (password.value.length < 8) {
                e.preventDefault();
                alert('Пароль должен содержать минимум 8 символов');
                password.style.borderColor = '#dc3545';
                return false;
            }
            const phonePattern = /^\+7\(\d{3}\)\d{3}-\d{2}-\d{2}$/;
            if (!phonePattern.test(phone.value)) {
                e.preventDefault();
                alert('Введите телефон в формате +7(XXX)XXX-XX-XX');
                phone.style.borderColor = '#dc3545';
                return false;
            }
            const loginPattern = /^[a-zA-Z0-9]{6,}$/;
            const login = document.getElementById('login');
            if (!loginPattern.test(login.value)) {
                e.preventDefault();
                alert('Логин должен содержать только латиницу и цифры, минимум 6 символов');
                login.style.borderColor = '#dc3545';
                return false;
            }
            const btn = document.getElementById('submitBtn');
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Регистрация...';
            btn.disabled = true;
        });
    }
</script>
</body>
</html>