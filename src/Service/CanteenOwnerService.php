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

    public function addMenuItemToMenu(int $canteenId, int $menuItemId, Schedule $schedule): void
    {
        
    }
}