<?php

require_once __DIR__ . "/../config/db.php";

use App\Models\Book;
use App\Controllers\BooksController;
use App\Models\Review;
use App\Controllers\ReviewsController;

if (isset($_GET['id'])) {
    $bookId = (int) $_GET['id'];
    $book = $bookController->getById($bookId);
    // ДЛЯ ПОЛУЧЕНИЯ РЕЙТИНГА ОБЩЕГО
    $averageRating = $ReviewsController->getAverageRatingByBookId($bookId);
    exit;
}

$bookModel = new Book($conn);
$booksController = new BooksController($bookModel);
$book = $booksController->getAllBooks();
?>


<?php $titleName = "Books"; ?>
<?php require_once "./layout/header.php"; ?>
<?php require_once "./layout/nav.php"; ?>

<div class="container">
    <div class="books" id="booksGrid">
        <div class="books__grid">
            <?php foreach ($book as $bookItem): ?>
                <?php
                $averageRating = $booksController->getAverageRatingByBookId($bookItem['id']);
                ?>
                <div class="books__item">
                    <div class="books__image">
                        <img src="<?= $bookItem['cover_image'] ?>" alt="<?= $bookItem['title'] ?>" class="books__img">
                    </div>
                    <div class="books__info">
                        <h3 class="books__title"><?= $bookItem['title'] ?></h3>
                        <span class="books__type"><?= $bookItem['genre'] ?></span>
                        <span class="books__type"><span><?= $bookItem['author'] ?></span> <span class="aveRate"><?= $averageRating ?: '0' ?></span> </span>
                        <a href="/book/<?= $bookItem['id']; ?>" class="podrobnee">Подробнее</a>
                    </div>
                </div>
            <?php endforeach; ?>


            <?php require_once "./layout/footer.php"; ?>