<?php

namespace App\Models;

use PDO;

class Book
{
    private $conn;

    public $id;
    public $title;
    public $author;
    public $genre;
    public $description;
    public $cover_image;
    public $created_at;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getAll()
    {
        $sql = "SELECT * FROM books";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getById($id)
    {
        $sql = "SELECT * FROM books WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    public function create()
    {
        $stmt = $this->conn->prepare("INSERT INTO books (title, author, genre, description, cover_image, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        return $stmt->execute([$this->title, $this->author, $this->genre, $this->description, $this->cover_image]);
    }

    public function delete($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM books WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getReviewsByBookId(int $bookId): array
    {
        $stmt = $this->conn->prepare("SELECT * FROM reviews WHERE book_id = ?");
        $stmt->execute([$bookId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAverageRatingByBookId(int $bookId): ?float
    {
        $stmt = $this->conn->prepare("SELECT AVG(rating) as avg_rating FROM reviews WHERE book_id = ?");
        $stmt->execute([$bookId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['avg_rating'] ? round($result['avg_rating'], 1) : null;
    }
}
