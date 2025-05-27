<?php
require_once __DIR__ . '/../config/function/auth.php';
require_once __DIR__ . '/../config/db.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Простая валидация
    if (strlen($username) < 2) {
        $errors[] = "Имя слишком короткое";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Некорректный email";
    }

    if (strlen($password) < 6) {
        $errors[] = "Пароль должен быть не менее 6 символов";
    }

    // Проверка на уникальность email
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $errors[] = "Email уже зарегистрирован";
    }

    // Добавление пользователя
    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");

        if ($stmt->execute([$username, $email, $hashedPassword])) {
            header("Location: /login");
            exit;
        } else {
            $errors[] = "Ошибка регистрации: " . $stmt->errorInfo()[2];
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

<body>

    <div class="bg">
        <div class="reg-block">
            <h2>Регистрация</h2>

            <?php if (!empty($errors)): ?>
                <ul style="color:red;">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <form method="post" action="/register" class="reg-form">
                <label>Имя пользователя<br>
                    <input type="text" name="username" required class="reg-input">
                </label><br><br>

                <label>Email<br>
                    <input type="email" name="email" required class="reg-input">
                </label><br><br>

                <label>Пароль<br>
                    <input type="password" name="password" required class="reg-input">
                </label><br><br>

                <div class="btns"><button class="btn" type="submit">Зарегистрироваться</button> <a class="main-redirect" href="/">На главную</a></div>

                <span>
                    Уже есть аккаунт? <a class="link" href="/login">Войти</a>
                </span>



            </form>


        </div>
    </div>

    <?php include 'layout/footer.php'; ?>