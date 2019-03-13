<?php

namespace App\Service;

use App\Models\Canteen;
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
}