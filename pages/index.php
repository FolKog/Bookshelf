<?php require_once __DIR__ . '/../config/db.php'; ?>
<?php include 'layout/header.php'; ?>
<?php include 'layout/nav.php'; ?>
<?php include 'include/books.php'; ?>

<script>
    const searchInput = document.getElementById('searchInput');
    const searchResults = document.getElementById('searchResults');

    // Функция для AJAX запроса
    async function searchBooks(query) {
        try {
            const response = await fetch(`/config/function/search.php?query=${encodeURIComponent(query)}`);
            const data = await response.json();
            return data.error ? [] : data;
        } catch (error) {
            console.error('Ошибка поиска:', error);
            return [];
        }
    }

    // Отображение результатов
    function displayResults(results) {
        if (results.length === 0) {
            searchResults.innerHTML = '<div class="no-results">Ничего не найдено</div>';
            return;
        }

        let html = `<div class="search-category">Книги ${results.length}</div>`;

        results.forEach(book => {
            const coverImage = book.cover_image || '/assets/img/default-book.jpg';
            const shortTitle = book.title.length > 30 ? book.title.substring(0, 30) + '...' : book.title;
            const shortAuthor = book.author.length > 25 ? book.author.substring(0, 25) + '...' : book.author;

            html += `
            <div class="search-item" onclick="selectBook(${book.id})">
                <img src="${coverImage}" alt="${book.title}" class="item-cover" onerror="this.src='/assets/img/default-book.jpg'">
                <div class="item-info">
                    <div class="item-title">${shortTitle}</div>
                    <div class="item-subtitle">${shortAuthor}</div>
                    <div class="item-meta">${book.genre}</div>
                </div>
            </div>
        `;
        });

        searchResults.innerHTML = html;
    }

    // Обработчик ввода с задержкой
    let searchTimeout;
    searchInput.addEventListener('input', function() {
        const query = this.value.trim();

        clearTimeout(searchTimeout);

        if (query.length >= 2) {
            searchTimeout = setTimeout(async () => {
                const results = await searchBooks(query);
                displayResults(results);
                searchResults.classList.add('show');
            }, 300);
        } else {
            searchResults.classList.remove('show');
        }
    });

    // Выбор книги из результатов
    function selectBook(bookId) {
        searchResults.classList.remove('show');
        window.location.href = `/pages/book.php?id=${bookId}`;
    }

    // Закрытие результатов при клике вне поиска
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.search-container')) {
            searchResults.classList.remove('show');
        }
    });

    // Закрытие по Escape
    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            searchResults.classList.remove('show');
            this.blur();
        }
    });
</script>

<?php include 'layout/footer.php'; ?>