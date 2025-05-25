<?php

require_once __DIR__ . '/../config/function/auth.php';
require_once __DIR__ . '/../config/function/functions.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\Book;
use App\Controllers\BooksController;
use App\Models\Review;
use App\Controllers\ReviewsController;

// Проверка, что параметр id передан и является числом
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $bookId = (int) $_GET['id'];
}

// Инициализация моделей и контроллеров
$bookModel = new Book($conn);
$booksController = new BooksController($bookModel);

$reviewModel = new Review($conn);
$reviewsController = new ReviewsController($reviewModel);

// Получение книги
$book = $booksController->getById($bookId);


// Получение отзывов и средней оценки
$reviews = $reviewsController->getReviewsByBookId($bookId);
$averageRating = $reviewsController->getAverageRatingByBookId($bookId);

// Проверка авторизации пользователя
if (!checkUserAuthorization()) {
    echo '<script>
        document.addEventListener("DOMContentLoaded", function() {
            const modal = document.getElementById("authModal");
            modal.style.display = "block";
        });
    </script>';
}

// Обработка формы добавления отзыва
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'], $_POST['rating'])) {
    if (!checkUserAuthorization()) {
        echo '<script>alert("Вы должны авторизоваться, чтобы оставить отзыв.");</script>';
    } else {
        $comment = htmlspecialchars($_POST['comment']);
        $rating = (int) $_POST['rating'];
        $userId = $_SESSION['user_id']; // ID авторизованного пользователя

        // Проверяем, что рейтинг находится в допустимом диапазоне
        if ($rating < 1 || $rating > 10) {
            echo '<script>alert("Рейтинг должен быть от 1 до 10.");</script>';
        } else {
            // Сохраняем отзыв в базе данных
            $stmt = $conn->prepare("INSERT INTO reviews (book_id, user_id, comment, rating, created_at) VALUES (:book_id, :user_id, :comment, :rating, NOW())");
            $stmt->execute([
                'book_id' => $bookId,
                'user_id' => $userId,
                'comment' => $comment,
                'rating' => $rating
            ]);

            echo '<script>alert("Ваш отзыв успешно добавлен!");</script>';
            header("Location: " . $_SERVER['REQUEST_URI']);
            exit;
        }
    }
}

// Проверка, оставлял ли пользователь отзыв
$userId = $_SESSION['user_id'] ?? null;
$userHasReviewed = false;

$stmt = $conn->prepare("SELECT r.comment, r.rating, r.created_at, u.username 
                        FROM reviews r 
                        JOIN users u ON r.user_id = u.id 
                        WHERE r.book_id = :book_id 
                        ORDER BY r.created_at DESC");
$stmt->execute(['book_id' => $book['id']]);
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Проверка, оставил ли пользователь отзыв
if ($userId) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM reviews WHERE book_id = :book_id AND user_id = :user_id");
    $stmt->execute([
        'book_id' => $book['id'],
        'user_id' => $userId
    ]);
    $userHasReviewed = (bool) $stmt->fetchColumn();
}

$titleName = "Book: " . htmlspecialchars($book['title']);

?>

<?php require_once __DIR__ . '/../layout/header.php'; ?>
<?php require_once __DIR__ . '/../layout/nav.php'; ?>

<div id="authModal" class="modal" style="display: none;">
    <div class="modal-content">
        <h2>Авторизация</h2>
        <p>Для просмотра этой страницы вам необходимо авторизоваться.</p>
        <a href="/login" class="btn btn-primary">Войти</a>
    </div>
</div>

<div class="container book-detail">
    <div class="image">
        <img src="<?= htmlspecialchars($book['cover_image']) ?>" alt="<?= htmlspecialchars($book['title']) ?>">
    </div>
    <div class="book-info">
        <p><strong>Средняя оценка:</strong> <?= $averageRating ?: 'Нет отзывов' ?></p>
        <div class="content">
            <h2><?= htmlspecialchars($book['title']) ?></h2>
            <p><strong>Автор:</strong> <?= htmlspecialchars($book['author']) ?></p>
            <p><strong>Жанр:</strong> <?= htmlspecialchars($book['genre']) ?></p>
        </div>
        <div class="desc">
            <p><?= nl2br(htmlspecialchars($book['description'])) ?></p>
        </div>
    </div>
</div>

<div class="reviews">
    <?php if (!$userHasReviewed): ?>
        <form action="" method="post">
            <input type="text" name="comment" placeholder="Ваш отзыв" required>
            <input type="number" name="rating" min="1" max="10" placeholder="Ваша оценка (1-10)" required>
            <button type="submit">Отправить отзыв</button>
        </form>
    <?php else: ?>
        <p class="reviewErr">Вы уже оставили отзыв для этой книги.</p>
    <?php endif; ?>

    <h2>Отзывы</h2>
    <div class="reviews-list">
        <?php if (empty($reviews)): ?>
            <p>Нет отзывов для этой книги.</p>
        <?php else: ?>
            <?php foreach ($reviews as $review): ?>
                <div class="review">
                    <div>
                        <p><strong><?= htmlspecialchars($review['username'] ?? 'Неизвестный пользователь') ?>:</strong></p>
                        <p><?= nl2br(htmlspecialchars($review['comment'] ?? '')) ?></p>
                        <p><strong>Рейтинг:</strong> <?= htmlspecialchars($review['rating'] ?? 'Нет рейтинга') ?></p>
                    </div>
                    <div>
                        <p><small>Добавлено: <?= isset($review['created_at']) ? date('d.m.Y H:i', strtotime($review['created_at'])) : 'Неизвестно' ?></small></p>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>