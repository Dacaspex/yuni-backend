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

    public function getCanteens(): array
    {
        return $this->storage->getCanteens();
    }
}