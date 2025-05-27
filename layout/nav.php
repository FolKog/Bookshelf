<?php
require_once __DIR__ . '/../config/function/auth.php';
?>
<header class="nnn">
    <div class="logo"></div>
    <div class="navbar container">
        <ul id="menu">
            <li><a href="/">Главная</a></li>
        </ul>
    </div>
    <div class="search-container">
        <div class="search-bar">
            <input type="text" id="searchInput" placeholder="Поиск книг, авторов, жанров...">
            <div class="search-results" id="searchResults"></div>
        </div>
    </div>
    <nav>
        <?php if (isLoggedIn()): ?>
            <a class="auth-register" href="/profile">Профиль</a>
            <a class="auth-login" href="/logout">Выйти</a>
        <?php else: ?>
            <a class="auth-login" href="/login">Вход</a>
            <a class="auth-register" href="/register">Регистрация</a>
        <?php endif; ?>
    </nav>
</header>