<?php

namespace App\Models;

use JsonSerializable;

class Schedule implements JsonSerializable
{
    /**
     * @var string
     */
    private $data;

    /**
     * @param string $data
     */
    public function __construct(string $data)
    {
        $this->data = $data;
    }

    public static function fromDatabase(string $data): Schedule
    {
        if (strlen($data) !== 7) {
            throw new \InvalidArgumentException('Schedule string is not 7 characters long');
        }

        return new Schedule($data);
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize()
    {
        return [
            'monday'    => $this->data[0] === '1',
            'tuesday'   => $this->data[1] === '1',
            'wednesday' => $this->data[2] === '1',
            'thursday'  => $this->data[3] === '1',
            'friday'    => $this->data[4] === '1',
            'saturday'  => $this->data[5] === '1',
            'sunday'    => $this->data[6] === '1'
        ];
    }
}