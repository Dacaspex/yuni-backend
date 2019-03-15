<?php

namespace App\Models;

class MenuItemReview extends Review
{
    /**
     * @var int
     */
    private $menuItemId;

    /**
     * @param int $id
     * @param int $rating
     * @param string $description
     * @param \DateTimeImmutable $createdAt
     * @param int $menuItemId
     */
    public function __construct(int $id, int $rating, string $description, \DateTimeImmutable $createdAt, int $menuItemId)
    {
        parent::__construct($id, $rating, $description, $createdAt);

        $this->menuItemId = $menuItemId;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return array_merge(
            parent::jsonSerialize(),
            [
                'menu_item_id' => $this->menuItemId,
            ]
        );
    }
}