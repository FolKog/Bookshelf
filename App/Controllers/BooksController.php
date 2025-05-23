<?php

namespace App\Controllers;

use App\Models\Book;

class BooksController
{
    private Book $book;

    public function __construct(Book $book)
    {
        $this->book = $book;
    }

    public function getAllBooks(): array
    {
        return $this->book->getAll();
    }

    public function getById(int $id): array
    {
        return $this->book->getById($id);
    }

    public function create(array $data): bool
    {
        $this->book->title = $data['title'];
        $this->book->author = $data['author'];
        $this->book->genre = $data['genre'];
        $this->book->description = $data['description'];
        $this->book->cover_image = $data['cover_image'] ?? null;

        return $this->book->create();
    }

    public function delete(int $id): bool
    {
        return $this->book->delete($id);
    }

    public function getReviewsByBookId($bookId): array
    {
        return $this->book->getReviewsByBookId($bookId);
    }

    public function getAverageRatingByBookId($bookId): ?float
    {
        return $this->book->getAverageRatingByBookId($bookId);
    }
}
