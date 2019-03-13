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
        $this->storage->addMenuItemToMenu($canteenId, $menuItemId, $schedule);
    }

    /**
     * @param int $menuId
     */
    public function removeItemFromMenu(int $menuId): void
    {
        $this->storage->removeItemFromMenu($menuId);
    }
}