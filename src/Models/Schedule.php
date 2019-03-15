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

    /**
     * @param string $data
     * @return Schedule
     */
    public static function fromBitMask(string $data): Schedule
    {
        if (strlen($data) !== 7) {
            throw new \InvalidArgumentException('Schedule string is not 7 characters long');
        }

        return new Schedule($data);
    }

    /**
     * @return string Bit mask representation of the schedule
     */
    public function toBitMask(): string
    {
        return $this->data;
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize()
    {
        return [
            'MONDAY'    => $this->data[0] === '1',
            'TUESDAY'   => $this->data[1] === '1',
            'WEDNESDAY' => $this->data[2] === '1',
            'THURSDAY'  => $this->data[3] === '1',
            'FRIDAY'    => $this->data[4] === '1',
            'SATURDAY'  => $this->data[5] === '1',
            'SUNDAY'    => $this->data[6] === '1'
        ];
    }
}