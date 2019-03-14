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
     * @param int $rating
     * @param string $description
     * @param string $createdAt
     * @param int $canteenId
     */
    public function __construct(int $id, int $rating, string $description, string $createdAt, int $canteenId)
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