<?php

namespace App\Models;

class Review implements \JsonSerializable
{
    /**
     * @var int
     */
    protected $id;
    /**
     * @var float between 1 <= n <= 5
     */
    protected $rating;
    /**
     * @var string|null
     */
    protected $description;
    /**
     * @var \DateTimeImmutable
     */
    protected $createdAt;

    /**
     * @param int $id
     * @param float $rating
     * @param string|null $description
     * @param \DateTimeImmutable $createdAt
     */
    public function __construct(int $id, float $rating, ?string $description, \DateTimeImmutable $createdAt)
    {
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