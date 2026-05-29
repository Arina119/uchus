<?php
session_start();

// Обработка вых
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header('Location: index.php');
    exit;
}

$is_admin = isset($_SESSION['admin']) && $_SESSION['admin'];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Учусь.РФ — дополнительное образование</title>
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
            color: #ffffff;
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
            background: rgba(255,255,255,0.15);
            color: #ffffff;
            border: 1px solid rgba(255,255,255,0.3);
            transition: 0.2s;
        }

        .nav-buttons a:hover {
            background: #0d47a1;
            transform: translateY(-2px);
        }

        /* Слайдер — уменьшенная ширина и высота */
        .slideshow-container {
            max-width: 900px;
            margin: 40px auto;
            position: relative;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        .mySlides {
            display: none;
        }

        .mySlides img {
            width: 100%;
            height: 400px;
            object-fit: cover;
        }

        .fade {
            animation: fadeIn 1s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0.4; }
            to { opacity: 1; }
        }

        .slide-text {
            position: absolute;
            bottom: 30px;
            left: 30px;
            background: rgba(13, 71, 161, 0.85);
            padding: 10px 20px;
            border-radius: 40px;
            font-size: 18px;
            font-weight: 600;
            color: #fff;
            backdrop-filter: blur(4px);
        }

        .prev, .next {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(0,0,0,0.5);
            color: white;
            border: none;
            cursor: pointer;
            padding: 10px 16px;
            border-radius: 50%;
            font-size: 18px;
            transition: 0.2s;
        }

        .prev { left: 20px; }
        .next { right: 20px; }

        .prev:hover, .next:hover {
            background: #0d47a1;
        }

        .dot-container {
            text-align: center;
            padding: 16px 0;
        }

        .dot {
            height: 12px;
            width: 12px;
            margin: 0 6px;
            background-color: #dee2e6;
            border-radius: 50%;
            display: inline-block;
            cursor: pointer;
            transition: 0.2s;
        }

        .dot.active, .dot:hover {
            background-color: #007bff;
            transform: scale(1.2);
        }

        .advantages {
            max-width: 1200px;
            margin: 60px auto;
            padding: 0 20px;
        }

        .advantages h2 {
            font-size: 32px;
            font-weight: 700;
            text-align: center;
            margin-bottom: 40px;
            color: #0d47a1;
        }

        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
        }

        .card {
            background: #ffffff;
            padding: 30px 24px;
            border-radius: 24px;
            text-align: center;
            box-shadow: 0 8px 20px rgba(0,0,0,0.05);
            transition: 0.2s;
            border: 1px solid #dee2e6;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
        }

        .card i {
            font-size: 48px;
            color: #007bff;
            margin-bottom: 20px;
        }

        .card h3 {
            font-size: 22px;
            font-weight: 600;
            margin-bottom: 12px;
            color: #0d47a1;
        }

        .card p {
            font-size: 16px;
            color: #495057;
            line-height: 1.5;
        }

        .footer {
            background: #0d47a1;
            color: #dee2e6;
            padding: 40px 20px 20px;
            margin-top: 60px;
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
            .slideshow-container {
                max-width: 100%;
                margin: 20px;
            }
            .mySlides img {
                height: 250px;
            }
            .slide-text {
                font-size: 14px;
                bottom: 15px;
                left: 15px;
                padding: 6px 14px;
            }
            .prev, .next {
                padding: 6px 12px;
                font-size: 14px;
            }
            .advantages h2 {
                font-size: 26px;
            }
            .cards {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            .card {
                padding: 20px;
            }
            .card i {
                font-size: 36px;
            }
            .card h3 {
                font-size: 20px;
            }
            .footer-content {
                grid-template-columns: 1fr;
                text-align: center;
            }
        }

        @media (max-width: 480px) {
            .mySlides img {
                height: 200px;
            }
            .slide-text {
                font-size: 12px;
                bottom: 10px;
                left: 10px;
                padding: 4px 10px;
            }
        }
    </style>
</head>
<body>

<header class="header">
    <div class="nav">
        <a href="index.php" class="logo">Учусь.РФ</a>
        <div class="nav-buttons">
            <?php if (!isset($_SESSION['user_id'])): ?>
                <a href="login.php">Войти</a>
                <a href="register.php">Регистрация</a>
            <?php elseif ($is_admin): ?>
                <a href="admin.php">Панель администратора</a>
                <a href="?logout=1">Выход</a>
            <?php elseif (isset($_SESSION['user_id'])): ?>
                <a href="history.php">Мои заявки</a>
                <a href="create.php">Новая заявка</a>
                <a href="?logout=1">Выход</a>
            <?php endif; ?>
        </div>
    </div>
</header>

<div class="slideshow-container">
    <div class="mySlides fade">
        <img src="Курсы.jpg" alt="Курсы">
        <div class="slide-text">Курсы повышения квалификации</div>
    </div>
    <div class="mySlides fade">
        <img src="Профессиональная переподготовка.png" alt="Переподготовка">
        <div class="slide-text">Профессиональная переподготовка</div>
    </div>
    <div class="mySlides fade">
        <img src="Охрана труда.jpg" alt="Охрана труда">
        <div class="slide-text">Курсы по охране труда</div>
    </div>
    <div class="mySlides fade">
        <img src="Педагог.jpg" alt="Педагогика">
        <div class="slide-text">Курсы для педагогов</div>
    </div>
    <a class="prev" onclick="plusSlides(-1)">❮</a>
    <a class="next" onclick="plusSlides(1)">❯</a>
</div>
<div class="dot-container">
    <span class="dot" onclick="currentSlide(1)"></span>
    <span class="dot" onclick="currentSlide(2)"></span>
    <span class="dot" onclick="currentSlide(3)"></span>
    <span class="dot" onclick="currentSlide(4)"></span>
</div>

<section class="advantages">
    <h2>Почему выбирают нас?</h2>
    <div class="cards">
        <div class="card">
            <i class="fas fa-chalkboard-user"></i>
            <h3>Опытные преподаватели</h3>
            <p>Высшая категория, практический опыт и индивидуальный подход.</p>
        </div>
        <div class="card">
            <i class="fas fa-laptop-code"></i>
            <h3>Современное оборудование</h3>
            <p>Новейшие программы и цифровые технологии обучения.</p>
        </div>
        <div class="card">
            <i class="fas fa-calendar-alt"></i>
            <h3>Гибкий график</h3>
            <p>Удобное время занятий, дистанционные форматы.</p>
        </div>
    </div>
</section>

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
    let slideIndex = 1;
    showSlides(slideIndex);

    function plusSlides(n) { showSlides(slideIndex += n); }
    function currentSlide(n) { showSlides(slideIndex = n); }

    function showSlides(n) {
        let slides = document.getElementsByClassName("mySlides");
        let dots = document.getElementsByClassName("dot");
        if (n > slides.length) slideIndex = 1;
        if (n < 1) slideIndex = slides.length;
        for (let i = 0; i < slides.length; i++) slides[i].style.display = "none";
        for (let i = 0; i < dots.length; i++) dots[i].className = dots[i].className.replace(" active", "");
        slides[slideIndex-1].style.display = "block";
        dots[slideIndex-1].className += " active";
    }

    let slideInterval = setInterval(() => plusSlides(1), 4000);
    const container = document.querySelector('.slideshow-container');
    container?.addEventListener('mouseenter', () => clearInterval(slideInterval));
    container?.addEventListener('mouseleave', () => slideInterval = setInterval(() => plusSlides(1), 4000));
</script>
</body>
</html>