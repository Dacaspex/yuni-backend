<?php

namespace App\Service;

use App\Models\Canteen;
use App\Models\CanteenReview;
use App\Models\MenuItemReview;
use App\Service\Exception\ReviewTooLongException;
use App\Storage\Storage;

class VisitorService
{
    /**
     * Maximum allowed number of characters for a review
     */
    private const REVIEW_CHAR_LIMIT = 200;
    /**
     * @var Storage
     */
    private $storage;

    /**
     * @param Storage $storage
     */
    public function __construct(Storage $storage)
    {
        $this->storage = $storage;
    }

    /**
     * @param int $id
     * @return Canteen
     * @throws \App\Storage\Exception\NotFoundException
     */
    public function getCanteen(int $id): Canteen
    {
        return $this->storage->getCanteen($id);
    }

    /**
     * @return array
     */
    public function getCanteens(): array
    {
        return $this->storage->getCanteens();
    }

    /**
     * @return array
     */
    public function getAllMenuItems(): array
    {
        return $this->storage->getAllMenuItems();
    }

    /**
     * @param int $menuItemId
     * @return MenuItemReview[]
     */
    public function getMenuItemReviews(int $menuItemId): array
    {
        return $this->storage->getMenuItemReviews($menuItemId);
    }

    /**
     * @param int $canteenId
     * @return CanteenReview[]
     */
    public function getCanteenReviews(int $canteenId): array
    {
        return $this->storage->getCanteenReviews($canteenId);
    }

    /**
     * @param int $menuItemId
     * @param float $rating
     * @param string $description
     * @throws ReviewTooLongException
     */
    public function createMenuItemReview(int $menuItemId, float $rating, string $description): void
    {
        if (strlen($description) > self::REVIEW_CHAR_LIMIT) {
            throw new ReviewTooLongException();
        }

        $this->storage->createMenuItemReview($menuItemId, $rating, $description);
    }

    /**
     * @param int $canteenId
     * @param float $rating
     * @param string $description
     * @throws ReviewTooLongException
     */
    public function createCanteenReview(int $canteenId, float $rating, string $description): void
    {
        if (strlen($description) > self::REVIEW_CHAR_LIMIT) {
            throw new ReviewTooLongException();
        }

        $this->storage->createCanteenReview($canteenId, $rating, $description);
    }
}