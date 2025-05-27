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
                <label for="avatar-upload" class="custom-file-label">Поменять Аватарку</label>
                <input type="file" id="avatar-upload" name="avatar" accept="image/*" required>
                <button type="submit">Подтвердить</button>
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

</style>

<?php require_once "./layout/footer.php"; ?>