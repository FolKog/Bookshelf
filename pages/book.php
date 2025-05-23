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
if (isset($_GET['id'])) {
    $movieId = (int) $_GET['id'];
    $movie = $MovieController->getById($movieId);
    // ДЛЯ ПОЛУЧЕНИЯ РЕЙТИНГА ОБЩЕГО
    $averageRating = $ReviewsController->getAverageRating($movieId);

    if ($movie === null) {
        echo "Фильм не найден";
        exit;
    }
}

// Инициализация модели и контроллера
$bookModel = new Book($conn);
$booksController = new BooksController($bookModel);

// Получение книги
$book = $booksController->getById($id);

// Если книга не найдена — редирект на 404
if (!$book) {
    header('Location: /404');
    exit;
}

$reviews = $booksController->getReviewsByBookId($id);
$averageRating = $booksController->getAverageRatingByBookId($id);
$reviewModel = new Review($conn);
$reviewsController = new ReviewsController($reviewModel);
$review = $reviewsController->getAllReviews();

if (!checkUserAuthorization()) {
    echo '<script>
        document.addEventListener("DOMContentLoaded", function() {
            const modal = document.getElementById("authModal");
            modal.style.display = "block";
        });
    </script>';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Проверяем, авторизован ли пользователь
    if (!checkUserAuthorization()) {
        echo '<script>alert("Вы должны авторизоваться, чтобы оставить отзыв.");</script>';
    } else {
        // Получаем данные из формы
        $comment = htmlspecialchars($_POST['comment']);
        $rating = (int)$_POST['rating'];
        $userId = $_SESSION['user_id']; // ID авторизованного пользователя
        $bookId = $book['id']; // ID текущей книги

        // Проверяем, что рейтинг находится в допустимом диапазоне
        if ($rating < 1 || $rating > 5) {
            echo '<script>alert("Рейтинг должен быть от 1 до 5.");</script>';
        } else {
            // Сохраняем отзыв в базе данных
            $stmt = $conn->prepare("INSERT INTO reviews (book_id, user_id, comment, rating) VALUES (:book_id, :user_id, :comment, :rating)");
            $stmt->execute([
                'book_id' => $bookId,
                'user_id' => $userId,
                'comment' => $comment,
                'rating' => $rating
            ]);

            echo '<script>alert("Ваш отзыв успешно добавлен!");</script>';
            // Перезагружаем страницу, чтобы отобразить новый отзыв
            header("Location: " . $_SERVER['REQUEST_URI']);
            exit;
        }
    }
}

$stmt = $conn->prepare("SELECT r.comment, r.rating, r.created_at, u.username 
                        FROM reviews r 
                        JOIN users u ON r.user_id = u.id 
                        WHERE r.book_id = :book_id 
                        ORDER BY r.created_at DESC");
$stmt->execute(['book_id' => $book['id']]);
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

$titleName = "Book: " . htmlspecialchars($book['title']);



?>

<?php require_once __DIR__ . '/../layout/header.php'; ?>
<?php require_once __DIR__ . '/../layout/nav.php'; ?>

<style>
    .modal {
        display: none;
        /* Скрыто по умолчанию */
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0, 0, 0, 0.5);
        /* Полупрозрачный фон */
    }

    .modal-content {
        background-color: #fff;
        margin: 15% auto;
        padding: 20px;
        border: 1px solid #888;
        width: 30%;
        border-radius: 8px;
        text-align: center;
    }

    .modal-content h2 {
        margin-top: 0;
    }

    .modal-content .btn {

        display: inline-block;
        margin-top: 15px;
        padding: 10px 20px;
        background-color: #007bff;
        color: #fff;
        text-decoration: none;
        border-radius: 5px;
        font-size: 16px;
    }

    .modal-content .btn:hover {
        background-color: #0056b3;
    }

    .close {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
    }

    .close:hover,
    .close:focus {
        color: black;
        text-decoration: none;
        cursor: pointer;
    }
</style>

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
    <form action="" method="post">
        <input type="text" name="comment" placeholder="Ваш отзыв" required>
        <input type="number" name="rating" min="1" max="5" placeholder="Ваша оценка (1-5)" required>
        <button type="submit">Отправить отзыв</button>
    </form>
    <h2>Отзывы</h2>
    <div class="reviews-list">
        <?php if (empty($reviews)): ?>
            <p>Нет отзывов для этой книги.</p>
        <?php else: ?>
            <?php foreach ($reviews as $review): ?>
                <div class="review">
                    <p><strong><?= htmlspecialchars($review['username']) ?>:</strong></p>
                    <p><?= nl2br(htmlspecialchars($review['comment'])) ?></p>
                    <p><strong>Рейтинг:</strong> <?= htmlspecialchars($review['rating']) ?></p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <style>
        /* Основной контейнер книги */
        .container.book-detail {
            display: flex;
            flex-wrap: wrap;
            margin: 20px auto;
            max-width: 1200px;
            gap: 20px;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        /* Изображение книги */
        .image {
            flex: 1;
            max-width: 300px;
            text-align: center;
        }

        .image img {
            max-width: 100%;
            height: auto;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        /* Информация о книге */
        .book-info {
            flex: 2;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .book-info .content h2 {
            font-size: 24px;
            margin-bottom: 10px;
            color: #333;
        }

        .book-info p {
            margin: 5px 0;
            font-size: 16px;
            color: #555;
        }

        .book-info .desc p {
            font-size: 14px;
            line-height: 1.6;
            color: #666;
        }

        /* Отзывы */
        .reviews {
            margin-top: 40px;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .reviews h2 {
            font-size: 20px;
            margin-bottom: 20px;
            color: #333;
        }

        .reviews-list .review {
            border-bottom: 1px solid #ddd;
            padding: 10px 0;
        }

        .reviews-list .review:last-child {
            border-bottom: none;
        }

        .reviews-list .review p {
            margin: 5px 0;
            font-size: 14px;
            color: #555;
        }

        .reviews-list .review strong {
            color: #007bff;
        }

        /* Форма для отправки отзыва */
        .reviews form {
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .reviews form input[type="text"],
        .reviews form input[type="number"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            box-sizing: border-box;
        }

        .reviews form input[type="text"]:focus,
        .reviews form input[type="number"]:focus {
            border-color: #007bff;
            outline: none;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
        }

        .reviews form button {
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .reviews form button:hover {
            background-color: #0056b3;
        }

        /* Модальное окно */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background-color: #fff;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 30%;
            border-radius: 8px;
            text-align: center;
        }

        .modal-content h2 {
            margin-top: 0;
            font-size: 20px;
            color: #333;
        }

        .modal-content p {
            font-size: 14px;
            color: #555;
        }

        .modal-content .btn {
            display: inline-block;
            margin-top: 15px;
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
        }

        .modal-content .btn:hover {
            background-color: #0056b3;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 40px;
            background-color: #fff;
            border-bottom: 1px solid #ccc;
        }

        .logo {
            background-image: url("/path-to-your-logo.svg");
            /* замените на свой путь */
            background-size: contain;
            background-repeat: no-repeat;
            width: 120px;
            height: 40px;
        }

        .navbar ul {
            display: flex;
            gap: 20px;
        }

        .navbar a,
        header nav a {
            font-size: 14px;
            color: #000;
            padding: 8px 12px;
            border-radius: 4px;
            transition: background 0.3s;
        }

        .navbar a:hover,
        header nav a:hover {
            background-color: #f0f0f0;
        }
    </style>

    <?php require_once __DIR__ . '/../layout/footer.php'; ?>