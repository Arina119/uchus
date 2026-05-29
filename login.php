<?php
session_start();

// Если пользователь уже авторизован
if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['admin']) && $_SESSION['admin']) {
        header('Location: admin.php');
    } else {
        header('Location: create.php');
    }
    exit;
}

$error = false;
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login = trim($_POST['login']);
    $password = $_POST['password'];
    
    if (empty($login) || empty($password)) {
        $error = true;
        $error_message = 'Пожалуйста, заполните все поля';
    } else {
        include('db.php');
        
        $stmt = $con->prepare("SELECT * FROM users WHERE login = ?");
        $stmt->bind_param("s", $login);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $error = true;
            $error_message = 'Неверный логин или пароль';
        } else {
            $user = $result->fetch_assoc();
            if ($password !== $user['password']) {
                $error = true;
                $error_message = 'Неверный логин или пароль';
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_login'] = $user['login'];
                $_SESSION['user_fullname'] = $user['fullname'];
                
                if ($user['login'] == 'Admin26') {
                    $_SESSION['admin'] = true;
                    header('Location: admin.php');
                } else {
                    header('Location: create.php');
                }
                exit;
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
    <title>Вход - Учусь.РФ</title>
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

        /* Шапка (как на главной) */
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

        /* Основной контейнер формы */
        .main-wrapper {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px 20px;
        }

        .container {
            max-width: 450px;
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

        .form-group {
            margin-bottom: 25px;
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
            padding: 14px 16px;
            border: 1px solid #ced4da;
            border-radius: 24px;
            font-size: 16px;
            font-family: 'Inter', sans-serif;
            transition: 0.2s;
            background: #fff;
        }

        .form-group input:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0,123,255,0.2);
        }

        .btn-login {
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
        }

        .btn-login:hover {
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

        .form-footer p {
            color: #6c757d;
            margin-bottom: 10px;
        }

        .register-link {
            color: #007bff;
            text-decoration: none;
            font-weight: 600;
            transition: 0.2s;
        }

        .register-link:hover {
            color: #0d47a1;
            text-decoration: underline;
        }

        .back-home {
            display: inline-block;
            margin-top: 12px;
            color: #6c757d;
            text-decoration: none;
            font-size: 14px;
            transition: 0.2s;
        }

        .back-home:hover {
            color: #007bff;
        }

        /* Футер (как на главной) */
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
            <a href="register.php">Регистрация</a>
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
            <h2>Добро пожаловать!</h2>
            <p>Войдите в свой аккаунт</p>
        </div>

        <?php if ($error): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-triangle"></i>
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" id="loginForm">
            <div class="form-group">
                <label for="login"><i class="fas fa-user"></i> Логин</label>
                <input type="text" id="login" name="login" 
                       value="<?php echo isset($_POST['login']) ? htmlspecialchars($_POST['login']) : ''; ?>"
                       placeholder="Введите ваш логин" required autofocus>
            </div>

            <div class="form-group">
                <label for="password"><i class="fas fa-lock"></i> Пароль</label>
                <input type="password" id="password" name="password" 
                       placeholder="Введите пароль" required>
            </div>

            <button type="submit" class="btn-login" id="submitBtn">
                <i class="fas fa-arrow-right"></i> Войти
            </button>
        </form>

        <div class="form-footer">
            <p>Нет аккаунта? <a href="register.php" class="register-link">Зарегистрироваться →</a></p>
            <a href="index.php" class="back-home">← Вернуться на главную</a>
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
        &copy; 2026 Учусь.РФ — все права защищены.
    </div>
</footer>

<script>
    const form = document.getElementById('loginForm');
    const submitBtn = document.getElementById('submitBtn');
    
    if (form) {
        form.addEventListener('submit', function(e) {
            const login = document.getElementById('login').value.trim();
            const password = document.getElementById('password').value;
            
            if (!login || !password) {
                e.preventDefault();
                showError('Пожалуйста, заполните все поля');
                return;
            }
            
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Вход...';
            submitBtn.style.opacity = '0.7';
            submitBtn.disabled = true;
            
            setTimeout(() => {
                submitBtn.innerHTML = '<i class="fas fa-arrow-right"></i> Войти';
                submitBtn.style.opacity = '1';
                submitBtn.disabled = false;
            }, 3000);
        });
    }
    
    function showError(message) {
        const existingError = document.querySelector('.error-message');
        if (existingError) existingError.remove();
        
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.innerHTML = `<i class="fas fa-exclamation-triangle"></i> ${message}`;
        
        const formHeader = document.querySelector('.form-header');
        formHeader.insertAdjacentElement('afterend', errorDiv);
        
        const container = document.querySelector('.container');
        container.style.animation = 'none';
        container.offsetHeight;
        container.style.animation = 'fadeInUp 0.5s ease-out';
    }
    
    const savedLogin = localStorage.getItem('savedLogin');
    if (savedLogin && !document.getElementById('login').value) {
        document.getElementById('login').value = savedLogin;
    }
    
    form.addEventListener('submit', function() {
        const login = document.getElementById('login').value;
        localStorage.setItem('savedLogin', login);
    });
</script>
</body>
</html>