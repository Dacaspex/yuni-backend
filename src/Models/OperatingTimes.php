<?php

namespace App\Models;

use JsonSerializable;

class OperatingTimes implements JsonSerializable
{
    /**
     * @var array Mapping between Day -> int
     */
    private $openingTimes;
    /**
     * @var array Mapping between Day -> int
     */
    private $closingTimes;

    /**
     * OperatingTimes constructor.
     * @param array $openingTimes
     * @param array $closingTimes
     */
    public function __construct(array $openingTimes, array $closingTimes)
    {
        $this->openingTimes = $openingTimes;
        $this->closingTimes = $closingTimes;
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize()
    {
        $json = [];

        foreach ($this->openingTimes as $day => $time) {
            $json[$day] = [
                'opening' => (int)$this->openingTimes[$day],
                'closing' => (int)$this->closingTimes[$day]
            ];
        }

        return $json;
    }
}