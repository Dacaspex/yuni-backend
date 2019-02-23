<?php

namespace App\Service;

use App\Storage\Storage;

class CanteenService
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
}