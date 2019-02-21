<?php

namespace App\Service;

use App\Storage\Storage;
use Vcn\Symfony\AutoFactory\AutoFactory;

class Factory implements AutoFactory
{
    /**
     * @param Storage $storage
     * @return CanteenService
     */
    public static function getCanteenService(Storage $storage): CanteenService
    {
        return new CanteenService($storage);
    }
}