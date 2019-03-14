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
     * @param int $id
     * @param string $name
     * @param string $description
     * @param Category $category
     * @param int $menuId
     * @param Schedule $schedule
     */
    public function __construct(
        int $id,
        string $name,
        string $description,
        Category $category,
        int $menuId,
        Schedule $schedule
    ) {
        parent::__construct($id, $name, $description, $category);

        $this->menuId   = $menuId;
        $this->schedule = $schedule;
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
                'menu_id'  => $this->menuId,
                'schedule' => $this->schedule,
            ]
        );
    }
}