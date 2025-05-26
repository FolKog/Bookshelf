<?php

namespace App\Controllers;

use App\Models\Review;
use Exception;

class ReviewsController
{
    private Review $review;

    public function __construct(Review $review)
    {
        $this->review = $review;
    }

    public function getAllReviews(): array
    {
        return $this->review->getAll();
    }

    public function createReview(array $data): bool
    {
        $this->review->user_id = $data['user_id'];
        $this->review->book_id = $data['book_id'];
        $this->review->comment = $data['comment'];
        $this->review->rating = $data['rating'];

        return $this->review->createReview();
    }

    public function delete($id, $userId): bool
    {
        return $this->review->deleteById($id, $userId);
    }

    public function update($id, $userId, $comment, $rating): bool
    {
        try {
            return $this->review->updateById($id, $userId, $comment, $rating);
        } catch (Exception $e) {
            error_log('Failed to update review: ' . $e->getMessage());
            return false;
        }
    }


    public function getReviewsByBookId(int $bookId): array
    {
        return $this->review->getReviewsByBookId($bookId);
    }

    public function getAverageRatingByBookId(int $bookId): ?float
    {
        return $this->review->getAverageRatingByBookId($bookId);
    }
}
