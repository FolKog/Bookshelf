<?php

namespace App\Models;

use PDO;

class Review
{
    private $conn;

    public $id;
    public $user_id;
    public $book_id;
    public $comment;
    public $rating;
    public $created_at;

    public function __construct(PDO $db)
    {
        $this->conn = $db;
    }

    public function getAll(): array
    {
        $sql = "SELECT * FROM reviews";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function createReview(): bool
    {
        $stmt = $this->conn->prepare("INSERT INTO reviews (user_id, book_id, comment, rating, created_at) VALUES (?, ?, ?, ?, NOW())");
        if (!$stmt->execute([$this->user_id, $this->book_id, $this->comment, $this->rating])) {
            throw new \Exception("Ошибка при создании отзыва: " . implode(", ", $stmt->errorInfo()));
        }
        return true;
    }

    public function deleteById($id, $userId): bool
    {
        $stmt = $this->conn->prepare("DELETE FROM reviews WHERE id = :id AND user_id = :user_id");
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $userId, \PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function updateById($id, $userId, $comment, $rating): bool
    {
        $stmt = $this->conn->prepare("
        UPDATE reviews 
        SET comment = :comment, rating = :rating 
        WHERE id = :id AND user_id = :user_id
    ");
        $stmt->bindParam(':comment', $comment, \PDO::PARAM_STR);
        $stmt->bindParam(':rating', $rating, \PDO::PARAM_INT);
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $userId, \PDO::PARAM_INT);
        return $stmt->execute();
    }


    public function getReviewsByBookId(int $bookId): array
    {
        $stmt = $this->conn->prepare("SELECT id, user_id, comment, rating, created_at FROM reviews WHERE book_id = ?");
        $stmt->execute([$bookId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getReviewByUserId(int $userId): array
    {
        $stmt = $this->conn->prepare("SELECT r.rating, r.comment, r.created_at, b.title 
                                      FROM reviews r 
                                      JOIN books b ON r.book_id = b.id 
                                      WHERE r.user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAverageRatingByBookId(int $bookId): ?float
    {
        $stmt = $this->conn->prepare("SELECT AVG(rating) as avg_rating FROM reviews WHERE book_id = ?");
        $stmt->execute([$bookId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['avg_rating'] ? round($result['avg_rating'], 1) : null;
    }

    public function reviewExists(int $id): bool
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM reviews WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetchColumn() > 0;
    }

    public function getReviewById(int $id): ?array
    {
        $stmt = $this->conn->prepare("SELECT * FROM reviews WHERE id = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function countReviewsByBookId(int $bookId): int
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) as review_count FROM reviews WHERE book_id = ?");
        $stmt->execute([$bookId]);
        return (int) $stmt->fetchColumn();
    }
}
