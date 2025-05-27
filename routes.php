<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/router.php';

get('/', 'pages/index.php');

get('/book/$id', 'pages/book.php');
post('/book/$id', 'pages/book.php');

get('/login', 'pages/login.php');
post('/login', 'pages/login.php');

get('/profile', 'pages/profile.php');
post('/profile', 'pages/profile.php');

get('/uploadAvatar', 'config/function/upload_avatar.php');
post('/uploadAvatar', 'config/function/upload_avatar.php');

get('/register', 'pages/register.php');
post('/register', 'pages/register.php');

get('/logout', 'config/logout.php');

get('/welcome', 'pages/welcome.php');


get('', 'api/delete_review.php');
get('', 'api/delete_review.php');
post('', 'api/review_action.php');

any('/404', 'pages/404.php');
