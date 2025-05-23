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

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getAll()
    {
        $sql = "SELECT * FROM reviews";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function updateReview()
    {
        $stmt = $this->conn->prepare("UPDATE reviews SET comment = ?, rating = ? WHERE id = ?");
        return $stmt->execute([$this->comment, $this->rating, $this->id]);
    }

    public function createReview()
    {
        $stmt = $this->conn->prepare("INSERT INTO reviews (user_id, book_id, comment, rating, created_at) VALUES (?, ?, ?, ?, NOW())");
        return $stmt->execute([$this->user_id, $this->book_id, $this->comment, $this->rating]);
    }

    public function deleteReview($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM reviews WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getReviewsByBookId(int $bookId): array
    {
        $stmt = $this->conn->prepare("SELECT * FROM reviews WHERE book_id = ?");
        $stmt->execute([$bookId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getReviewByUserId($userId)
    {
        $stmt = $this->conn->prepare("SELECT r.rating, r.comment, r.created_at, b.title 
                                  FROM reviews r 
                                  JOIN books b ON r.book_id = b.id 
                                  WHERE r.user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC); // <--- Важно!
    }

    public function getAverageRatingByBookId(int $bookId): ?float
    {
        $stmt = $this->conn->prepare("SELECT AVG(rating) as avg_rating FROM reviews WHERE book_id = ?");
        $stmt->execute([$bookId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['avg_rating'] ? round($result['avg_rating'], 1) : null;
    }
}
