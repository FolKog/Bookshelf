<?php

require_once __DIR__ . "/../config/db.php";

use App\Models\Book;
use App\Controllers\BooksController;

$bookModel = new Book($conn);
$booksController = new BooksController($bookModel);
$book = $booksController->getAllBooks();
?>


<?php $titleName = "Books"; ?>
<?php require_once "./layout/header.php"; ?>
<?php require_once "./layout/nav.php"; ?>

<div class="container">
    <div class="books">
        <div class="books__grid">
            <?php foreach ($book as $bookItem): ?>
                <div class="books__item">
                    <div class="books__image">
                        <img src="<?= $bookItem['cover_image'] ?>" alt="<?= $bookItem['title'] ?>" class="books__img">
                    </div>
                    <div class="books__info">
                        <h3 class="books__title"><?= $bookItem['title'] ?></h3>
                        <span class="books__type"><?= $bookItem['genre'] ?></span>
                        <span class="books__type"><?= $bookItem['author'] ?></span>
                        <a href="/book/<?= $bookItem['id']; ?>" class="podrobnee">Подробнее</a>
                    </div>
                </div>
            <?php endforeach; ?>