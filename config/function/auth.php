<?php
session_start();

function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']);
}

function requireAuth(): void
{
    if (!isLoggedIn()) {
        header('Location: /login');
        exit;
    }
}
function checkUserAuthorization(): bool
{

    if (isset($_SESSION['user_id'])) {
        return true;
    }

    return false;
}
