<?php

use App\Models\Book;
use App\Controllers\BooksController;

$bookModel = new Book($conn);
$booksController = new BooksController($bookModel);
$books = $booksController->getAllBooks();

// Получение уникальных жанров для фильтрации
$genres = array_unique(array_column($books, 'genre'));
?>

<?php $titleName = "Books"; ?>
<?php require_once "./layout/header.php"; ?>
<?php require_once "./layout/nav.php"; ?>

<div class="container">
    <!-- Кнопка для открытия модального окна -->
    <button id="openModal" class="btn btn-primary filter">Фильтровать по жанрам</button>

    <!-- Модальное окно -->
    <div id="genreModal" class="modal">
        <div class="modal-content">
            <span id="closeModal" class="close">&times;</span>
            <h3>Выберите жанры</h3>
            <ul id="genreFilter">
                <?php foreach ($genres as $index => $genre): ?>
                    <li>
                        <input type="checkbox" id="genre_<?= $index ?>" class="filter-checkbox" name="genre" data-filter="genre" value="<?= htmlspecialchars($genre) ?>">
                        <label for="genre_<?= $index ?>"><?= htmlspecialchars($genre) ?></label>
                    </li>
                <?php endforeach; ?>
            </ul>
            <button id="applyFilters" class="btn btn-primary">Применить</button>
        </div>
    </div>

    <!-- Список книг -->
    <div class="books" id="booksGrid">
        <div class="books__grid">
            <?php foreach ($books as $bookItem): ?>
                <div class="books__item" data-genre="<?= htmlspecialchars($bookItem['genre']) ?>">
                    <div class="books__image">
                        <img src="<?= htmlspecialchars($bookItem['cover_image']) ?>" alt="<?= htmlspecialchars($bookItem['title']) ?>" class="books__img">
                    </div>
                    <div class="books__info">
                        <h3 class="books__title"><?= htmlspecialchars($bookItem['title']) ?></h3>
                        <span class="books__type"><?= htmlspecialchars($bookItem['genre']) ?></span>
                        <span class="books__type"><?= htmlspecialchars($bookItem['author']) ?></span>
                        <a href="/book/<?= $bookItem['id']; ?>" class="podrobnee">Подробнее</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <div id="noBooksMessage" style="display: none; text-align: center; font-size: 18px; color: #555; margin-top: 20px;">
        Книги не найдены.
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('genreModal');
        const openModalBtn = document.getElementById('openModal');
        const closeModalBtn = document.getElementById('closeModal');
        const applyFiltersBtn = document.getElementById('applyFilters');
        const checkboxes = document.querySelectorAll('.filter-checkbox');
        const books = document.querySelectorAll('.books__item');
        const searchInput = document.getElementById('searchInput');

        // Открытие модального окна
        openModalBtn.addEventListener('click', function() {
            modal.style.display = 'block';
        });

        // Закрытие модального окна
        closeModalBtn.addEventListener('click', function() {
            modal.style.display = 'none';
        });

        // Закрытие модального окна при клике вне его
        window.addEventListener('click', function(event) {
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        });

        // Функция для фильтрации книг
        // Функция для фильтрации книг
        function filterBooks() {
            const activeGenres = Array.from(document.querySelectorAll('input[data-filter="genre"]:checked')).map(cb => cb.value.toLowerCase());
            const searchQuery = searchInput.value.toLowerCase();

            let hasVisibleBooks = false; // Флаг для проверки наличия видимых книг

            books.forEach(function(book) {
                const title = book.querySelector('.books__title')?.textContent.toLowerCase() || '';
                const author = book.querySelector('.books__type')?.textContent.toLowerCase() || '';
                const genre = book.getAttribute('data-genre').toLowerCase();

                const matchesGenre = activeGenres.length === 0 || activeGenres.includes(genre);
                const matchesSearch = title.includes(searchQuery) || author.includes(searchQuery) || genre.includes(searchQuery);

                if (matchesGenre && matchesSearch) {
                    book.style.display = ''; // Показываем книгу
                    hasVisibleBooks = true; // Найдена хотя бы одна книга
                } else {
                    book.style.display = 'none'; // Скрываем книгу
                }
            });

            // Показываем или скрываем сообщение "Книги не найдены"
            const noBooksMessage = document.getElementById('noBooksMessage');
            if (!hasVisibleBooks) {
                noBooksMessage.style.display = 'block'; // Показываем сообщение
            } else {
                noBooksMessage.style.display = 'none'; // Скрываем сообщение
            }
        }

        // Применение фильтров по жанрам
        applyFiltersBtn.addEventListener('click', function() {
            filterBooks();
            modal.style.display = 'none'; // Закрываем модальное окно
        });

        // Поиск по названию, автору и жанру
        searchInput.addEventListener('input', function() {
            filterBooks();
        });
    });
</script>

<style>
    /* Стили для модального окна */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.8);
        overflow: auto;
    }

    .modal-content {
        background-color: #fff;
        margin: 10% auto;
        padding: 20px;
        width: 90%;
        max-width: 500px;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        animation: fadeIn 0.3s ease-in-out;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .close {
        color: #333;
        float: right;
        font-size: 24px;
        font-weight: bold;
        cursor: pointer;
        transition: color 0.3s ease;
    }

    .close:hover,
    .close:focus {
        color: #ff0000;
        text-decoration: none;
    }

    .modal h3 {
        margin-top: 0;
        font-size: 22px;
        color: #333;
        text-align: center;
    }

    #genreFilter {
        list-style: none;
        padding: 0;
        margin: 20px 0;
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        justify-content: center;
    }

    #genreFilter li {
        display: inline-block;
    }

    #genreFilter label {
        display: inline-block;
        padding: 10px 15px;
        margin: 5px;
        font-size: 16px;
        background-color: #f9f9f9;
        border: 1px solid #ddd;
        border-radius: 8px;
        cursor: pointer;
    }

    .filter-checkbox {
        display: none;
        /* Скрываем чекбоксы */
    }

    #genreFilter label:hover {
        background-color: #e6e6e6;
        border-color: #ccc;
    }

    .filter-checkbox:checked+label {
        background-color: #007bff;
        color: #007bff;
        border-color: #007bff;
    }

    #applyFilters {
        display: block;
        margin: 20px auto 0;
        padding: 10px 20px;
        font-size: 16px;
        background-color: #007bff;
        color: #fff;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    #applyFilters:hover {
        background-color: #0056b3;
    }
</style>

<?php require_once "./layout/footer.php"; ?>