<?php

namespace App\Controllers;

use App\Models\Review;

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

    public function updateReview(array $data): bool
    {
        $this->review->id = $data['id'];
        $this->review->comment = $data['comment'];
        $this->review->rating = $data['rating'];

        return $this->review->updateReview();
    }

    public function createReview(array $data): bool
    {
        $this->review->user_id = $data['user_id'];
        $this->review->book_id = $data['book_id'];
        $this->review->comment = $data['comment'];
        $this->review->rating = $data['rating'];

        return $this->review->createReview();
    }

    public function deleteReview(int $id): bool
    {
        return $this->review->deleteReview($id);
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
