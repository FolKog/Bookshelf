document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const books = document.querySelectorAll('.books__item');

    searchInput.addEventListener('input', function() {
        const filter = this.value.toLowerCase();

        books.forEach(function(book) {
            const title = book.querySelector('.books__title')?.textContent.toLowerCase() || '';
            const author = book.querySelector('.books__author')?.textContent.toLowerCase() || '';
            const genre = book.querySelector('.books__genre')?.textContent.toLowerCase() || '';

            if (title.includes(filter) || author.includes(filter) || genre.includes(filter)) {
                book.style.display = ''; // Показываем книгу
            } else {
                book.style.display = 'none'; // Скрываем книгу
            }
        });
    });
});