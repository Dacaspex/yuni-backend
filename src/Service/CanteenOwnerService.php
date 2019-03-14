<?php

namespace App\Service;

use App\Models\Schedule;
use App\Storage\Storage;

class CanteenOwnerService
{
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
     * @param int $canteenId
     * @param int $menuItemId
     * @param Schedule $schedule
     */
    public function addMenuItemToMenu(int $canteenId, int $menuItemId, Schedule $schedule): void
    {
        // Check if the menu item is not already on the menu of the canteen, of so, abort operation
        foreach ($this->storage->getMenuItems($canteenId) as $item) {
            if ($item->getId() === $menuItemId) {
                return;
            }
        }

        $this->storage->addMenuItemToMenu($canteenId, $menuItemId, $schedule);
    }

    /**
     * @param int $menuId
     * @throws \App\Storage\Exception\NotFoundException
     */
    public function removeItemFromMenu(int $menuId): void
    {
        $this->storage->removeItemFromMenu($menuId);
    }
}