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
    .bg {
        background-image: url("../assets/img/auth-bg.png");
        background-size: 100% 100%;
        background-position: center top;
        height: 100vh;
    }

    .reg-block {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background-color: rgba(255, 255, 255, 0.8);
        padding: 20px 0 0 0;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }

    .reg-block h2 {
        text-align: center;
        font-size: 24px;
        margin-bottom: 20px;
    }

    .reg-form {
        display: flex;
        flex-direction: column;
        align-items: center;
        background-color: white;
        border-radius: 10px;
        padding: 45px 50px;
    }

    .reg-input {
        width: 320px;
        padding: 10px;
        margin-bottom: 10px;
        border: 1px solid #ccc;
        border-radius: 5px;
    }

    .reg-input:focus {
        border-color: #007bff;
        outline: none;
    }

    .btn {
        padding: 10px 20px;
        background-color: black;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        width: 50%;
    }

    .btns {
        display: flex;
        justify-content: space-between;
        width: 100%;
        margin-bottom: 20px;
    }

    .main-redirect {
        border-radius: 10px;
        border: 1px solid black;
        color: black;
        padding: 10px 20px;
    }

    .link {
        color: black;
        font-weight: bold;
    }
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