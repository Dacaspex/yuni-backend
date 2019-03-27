<?php

namespace App\Models;

class ExtendedMenuItem extends MenuItem
{
    /**
     * @var int
     */
    private $menuId;
    /**
     * @var Schedule
     */
    private $schedule;
    /**
     * @var Availability
     */
    private $availability;

    /**
     * @param int $id
     * @param string $name
     * @param string $description
     * @param Category $category
     * @param float|null $rating
     * @param int $menuId
     * @param Schedule $schedule
     * @param Availability $availability
     */
    public function __construct(
        int $id,
        string $name,
        string $description,
        Category $category,
        ?float $rating,
        int $menuId,
        Schedule $schedule,
        Availability $availability
    ) {
        parent::__construct($id, $name, $description, $category, $rating);

        $this->menuId       = $menuId;
        $this->schedule     = $schedule;
        $this->availability = $availability;
    }

    /**
     * @return int
     */
    public function getMenuId(): int
    {
        return $this->menuId;
    }

    /**
     * @return Schedule
     */
    public function getSchedule(): Schedule
    {
        return $this->schedule;
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize()
    {
        return array_merge(
            parent::jsonSerialize(),
            [
                'menu_id'      => $this->menuId,
                'schedule'     => $this->schedule,
                'availability' => $this->availability->getName(),
            ]
        );
    }
}