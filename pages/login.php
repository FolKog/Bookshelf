<?php
session_start();
require_once __DIR__ . '/../config/db.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Валидация
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Введите корректный email";
    }

    if (empty($password)) {
        $errors[] = "Введите пароль";
    }

    // Поиск пользователя
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Успешная авторизация
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];

            header("Location: /profile");
            exit;
        } else {
            $errors[] = "Неверный email или пароль";
        }
    }
}

if (!empty($_SESSION['user_id'])) {
    header("Location: /profile");
    exit;
}
?>
<?php require_once __DIR__ . '/../layout/header.php'; ?>

<style>

</style>

<?php include 'layout/header.php'; ?>

<div class="bg">
    <div class="reg-block">
        <h2>Вход</h2>


        <?php if (!empty($errors)): ?>
            <ul style="color:red;">
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <form method="post" action="/login" class="reg-form">
            <label>Email<br>
                <input type="email" name="email" required class="reg-input">
            </label><br><br>
            <label>Пароль<br>
                <input type="password" name="password" required class="reg-input">
            </label><br><br>

            <div class="btns"><button class="btn" type="submit">Войти</button> <a class="main-redirect" href="/">На главную</a></div>

            <span>
                Нет аккаунта? <a class="link" href="/register">Зарегистрироваться</a>
            </span>



        </form>


    </div>
</div>
<?php include 'layout/footer.php'; ?>