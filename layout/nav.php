<?php
require_once __DIR__ . '/../config/function/auth.php';
?>
<header>
    <div class="logo"></div>
    <div class="navbar">
        <ul>
            <li><a href="/">Главная</a></li>
            <li><a href="#">О нас</a></li>
            <li><a href="#">Контакты</a></li>
        </ul>
    </div>
    <nav>
        <?php if (isLoggedIn()): ?>
            <a href="/profile">Профиль</a>
            <a href="/logout">Выйти</a>
        <?php else: ?>
            <a href="/login">Вход</a>
            <a href="/register">Регистрация</a>
        <?php endif; ?>
    </nav>

</header>