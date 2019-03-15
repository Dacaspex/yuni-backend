<?php

namespace App\Models;

class Review implements \JsonSerializable
{
    /**
     * @var int
     */
    protected $id;
    /**
     * @var int between 1 <= n <= 5
     */
    protected $rating;
    /**
     * @var string
     */
    protected $description;
    /**
     * @var \DateTimeImmutable
     */
    protected $createdAt;

    /**
     * @param int $id
     * @param int $rating
     * @param string $description
     * @param \DateTimeImmutable $createdAt
     */
    public function __construct(int $id, int $rating, string $description, \DateTimeImmutable $createdAt)
    {
        if ($rating < 1 || $rating > 5) {
            throw new \InvalidArgumentException('Rating out of bounds [1, 5]');
        }

        $this->id          = $id;
        $this->rating      = $rating;
        $this->description = $description;
        $this->createdAt   = $createdAt;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'id'          => $this->id,
            'rating'      => $this->rating,
            'description' => $this->description,
            'created_at'  => $this->createdAt->format(DATE_ATOM),
        ];
    }
}