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
     * @var float|null
     */
    private $rating;
    /**
     * @var int
     */
    private $busyness;
    /**
     * @var MenuItem[]
     */
    private $menuItems;

    /**
     * @param int $id
     * @param string $name
     * @param string $description
     * @param string $building
     * @param float $longitude
     * @param float $latitude
     * @param OperatingTimes $operatingTimes
     * @param float|null $rating
     * @param int $busyness
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
        ?float $rating,
        int $busyness,
        array $menuItems
    ) {
        $this->id             = $id;
        $this->name           = $name;
        $this->description    = $description;
        $this->building       = $building;
        $this->longitude      = $longitude;
        $this->latitude       = $latitude;
        $this->operatingTimes = $operatingTimes;
        $this->rating         = $rating;
        $this->busyness       = $busyness;
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
            'rating'          => $this->rating,
            'busyness'        => $this->busyness,
            'menu_items'      => $this->menuItems,
        ];
    }
}