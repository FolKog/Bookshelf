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
            <a href="/profile">Профиль</a>
            <a href="/logout">Выйти</a>
        <?php else: ?>
            <a href="/login">Вход</a>
            <a href="/register">Регистрация</a>
        <?php endif; ?>
    </nav>
</header>