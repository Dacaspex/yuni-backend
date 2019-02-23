<?php

namespace App\Models;

use JsonSerializable;

class MenuItem implements JsonSerializable
{
    /**
     * @var int
     */
    private $id;
    /**
     * @var int
     */
    private $globalId;
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
    private $category;
    /**
     * @var Schedule
     */
    private $schedule;

    /**
     * MenuItem constructor.
     * @param int $id
     * @param int $globalId
     * @param string $name
     * @param string $description
     * @param Schedule $schedule
     */
    public function __construct(
        int $id,
        int $globalId,
        string $name,
        string $description,
        string $category,
        Schedule $schedule
    ) {
        $this->id          = $id;
        $this->globalId    = $globalId;
        $this->name        = $name;
        $this->description = $description;
        $this->category    = $category;
        $this->schedule    = $schedule;
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize()
    {
        return [
            'id'          => $this->id,
            'global_id'   => $this->globalId,
            'name'        => $this->name,
            'description' => $this->description,
            'category'    => $this->category,
            'schedule'    => $this->schedule,
        ];
    }
}