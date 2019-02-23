<?php

namespace App\Models;

use JsonSerializable;

class Canteen implements JsonSerializable
{
    /**
     * @var int
     */
    private $id;
    /**
     * @var string
     */
    private $name;
    /**
     * @var string
     */
    private $description;
    /**
     * @var string
     */
    private $building;
    /**
     * @var float
     */
    private $longitude;
    /**
     * @var float
     */
    private $latitude;
    /**
     * @var OperatingTimes
     */
    private $operatingTimes;
    /**
     * @var MenuItem[]
     */
    private $menuItems;

    /**
     * Canteen constructor.
     * @param int $id
     * @param string $name
     * @param string $description
     * @param string $building
     * @param float $longitude
     * @param float $latitude
     * @param OperatingTimes $operatingTimes
     * @param MenuItem[] $menuItems
     */
    public function __construct(
        int $id,
        string $name,
        string $description,
        string $building,
        float $longitude,
        float $latitude,
        OperatingTimes $operatingTimes,
        array $menuItems
    ) {
        $this->id             = $id;
        $this->name           = $name;
        $this->description    = $description;
        $this->building       = $building;
        $this->longitude      = $longitude;
        $this->latitude       = $latitude;
        $this->operatingTimes = $operatingTimes;
        $this->menuItems      = $menuItems;
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize()
    {
        return [
            'id'              => $this->id,
            'name'            => $this->name,
            'description'     => $this->description,
            'building'        => $this->building,
            'longitude'       => $this->longitude,
            'latitude'        => $this->latitude,
            'operating_times' => $this->operatingTimes,
            'menu_items'      => $this->menuItems,
        ];
    }
}