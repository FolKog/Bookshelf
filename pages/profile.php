<?php
require_once __DIR__ . '/../config/function/auth.php';
requireAuth();

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../App/Models/Review.php';

use App\Models\Review;

$userId = $_SESSION['user_id'] ?? null;

if (!$userId) {
    header('Location: /register');
    exit;
}

$query = "SELECT username, is_admin, email, avatar FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    echo "Пользователь не найден";
    exit;
}

$username = htmlspecialchars($user['username']);
$email = htmlspecialchars($user['email']);
$isAdmin = (bool) $user['is_admin'];
$avatar = $user['avatar'] ?? '/assets/default-avatar.png';

$reviewModel = new Review($conn);
$userReviews = $reviewModel->getReviewByUserId($userId);
?>

<?php $titleName = 'Profile' ?>
<?php $titlePage = 'Личный кабинет' ?>
<?php require_once "./layout/header.php"; ?>
<?php require_once "./layout/nav.php"; ?>

<main class="profile-container">
    <section class="profile-header">
        <img src="<?= $avatar ?>" alt="Аватар" class="avatar">
        <div class="user-info">
            <h1><?= $username ?></h1>
            <p>Email: <strong><?= $email ?></strong></p>
            <form action="/uploadAvatar" method="post" enctype="multipart/form-data" class="avatar-form">
                <label for="avatar-upload" class="custom-file-label">Выбрать файл</label>
                <input type="file" id="avatar-upload" name="avatar" accept="image/*" required>
                <button type="submit">Загрузить</button>
            </form>

        </div>
    </section>

    <section class="profile-links">
        <span class="role"><?= $isAdmin ? 'Администратор' : 'Пользователь' ?></span>
        <div class="links">
            <a href="/">Главная</a>
            <a href="/logout">Выход</a>
        </div>
    </section>

    <section class="reviews-section">
        <h2>Мои отзывы</h2>
        <?php if (empty($userReviews)): ?>
            <p class="empty">Вы ещё не оставили отзывов.</p>
        <?php else: ?>
            <ul class="reviews">
                <?php foreach ($userReviews as $review): ?>
                    <li class="review">
                        <h3><?= htmlspecialchars($review['title']) ?></h3>
                        <div class="review-meta">
                            <span>Оценка: <?= $review['rating'] ?>/5</span>
                            <time><?= date('d.m.Y H:i', strtotime($review['created_at'])) ?></time>
                        </div>
                        <blockquote><?= htmlspecialchars($review['comment']) ?></blockquote>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>
</main>

<style>
    :root {
        --bg-light: #ffffff;
        --bg-grey: #f5f5f5;
        --text-dark: #111111;
        --text-muted: #666666;
        --primary: #000000;
        --border-color: #dddddd;
    }

    body {
        background: var(--bg-light);
        color: var(--text-dark);
        font-family: 'Segoe UI', sans-serif;
        margin: 0;
        padding: 0;
    }

    .profile-container {
        max-width: 1000px;
        margin: 2rem auto;
        padding: 1rem;
    }

    .profile-header {
        background: var(--bg-grey);
        border-radius: 12px;
        padding: 2rem;
        display: flex;
        gap: 2rem;
        align-items: center;
        border: 1px solid var(--border-color);
    }

    .avatar {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid var(--primary);
    }

    .user-info h1 {
        margin: 0;
        font-size: 1.8rem;
        color: var(--primary);
    }

    .avatar-form {
        margin-top: 1rem;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .avatar-form input {
        border: 1px solid var(--border-color);
        padding: 0.5rem;
        color: var(--text-dark);
        background: #fff;
    }

    .avatar-form button {
        background: var(--primary);
        border: none;
        padding: 0.6rem 1.2rem;
        color: #fff;
        font-weight: bold;
        border-radius: 6px;
        cursor: pointer;
    }

    .profile-links {
        margin: 2rem 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
    }

    .profile-links .role {
        text-transform: uppercase;
        color: var(--text-muted);
        font-weight: bold;
    }

    .profile-links .links a {
        margin: 0.5rem;
        background: var(--primary);
        padding: 0.5rem 1rem;
        border-radius: 6px;
        text-decoration: none;
        color: #fff;
        font-weight: 600;
    }

    .reviews-section {
        background: var(--bg-grey);
        padding: 1.5rem;
        border-radius: 12px;
        border: 1px solid var(--border-color);
    }

    .reviews-section h2 {
        margin-bottom: 1rem;
        color: var(--primary);
    }

    .reviews {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .review {
        background: #fff;
        padding: 1rem;
        border-radius: 8px;
        border: 1px solid var(--border-color);
    }

    .review h3 {
        margin: 0 0 0.5rem;
        font-size: 1.2rem;
        color: var(--primary);
    }

    .review-meta {
        display: flex;
        justify-content: space-between;
        font-size: 0.9rem;
        color: var(--text-muted);
    }

    .review blockquote {
        margin: 0.8rem 0 0;
        color: var(--text-dark);
        font-style: italic;
    }

    .empty {
        color: var(--text-muted);
        font-style: italic;
    }

    @media (max-width: 600px) {
        .profile-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .avatar {
            width: 100px;
            height: 100px;
        }
    }

    /* Скрываем оригинальный input */
    .avatar-form input[type="file"] {
        display: none;
    }

    /* Кастомная кнопка для выбора файла */
    .custom-file-label {
        display: inline-block;
        padding: 0.6rem 1.2rem;
        background-color: var(--primary);
        color: #fff;
        font-weight: bold;
        border-radius: 6px;
        cursor: pointer;
        text-align: center;
        margin-bottom: 0.5rem;
        transition: background 0.3s;
    }

    .custom-file-label:hover {
        background-color: #222;
    }
</style>

<?php require_once "./layout/footer.php"; ?>