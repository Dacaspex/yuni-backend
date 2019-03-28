<?php

namespace App\Models;

use JsonSerializable;
use Vcn\Pipette\Json;

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
     * Flattens and combines the closing and opening times into one array
     * [day, opening time, closing time]
     *
     * @return array
     */
    public function getEntries(): array
    {
        $entries = [];
        foreach ($this->openingTimes as $day => $openingTime) {
            $entries[] = [
                'day'     => $day,
                'opening' => $openingTime,
                'closing' => $this->closingTimes[$day]
            ];
        }

        return $entries;
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

    /**
     * @param Json\Value $json
     * @return OperatingTimes
     * @throws Json\Exception\AssertionFailed
     */
    public static function fromJson(Json\Value $json): OperatingTimes
    {
        $openingTimes = [];
        $closingTimes = [];

        $json->objectMapWithIndex(
            function (string $day, Json\Value $json) use (&$openingTimes, &$closingTimes) {
                $json->objectMapWithIndex(
                    function (string $type, Json\Value $json) use (&$openingTimes, &$closingTimes, $day) {
                        if ($type === 'opening') {
                            $openingTimes[$day] = $json->int();
                        }
                        if ($type === 'closing') {
                            $closingTimes[$day] = $json->int();
                        }
                    }
                );
            }
        );

        return new OperatingTimes($openingTimes, $closingTimes);
    }
}