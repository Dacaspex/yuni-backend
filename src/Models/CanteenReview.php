<?php

namespace App\Models;

class CanteenReview extends Review
{
    /**
     * @var int
     */
    private $canteenId;

    /**
     * @param int $id
     * @param float $rating
     * @param string|null $description
     * @param \DateTimeImmutable $createdAt
     * @param int $canteenId
     */
    public function __construct(int $id, float $rating, ?string $description, \DateTimeImmutable $createdAt, int $canteenId)
    {
        parent::__construct($id, $rating, $description, $createdAt);

        $this->canteenId = $canteenId;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return array_merge(
            parent::jsonSerialize(),
            [
                'canteen_id' => $this->canteenId,
            ]
        );
    }
}