<?php

namespace App\Models;

use JsonSerializable;

class MenuItem implements JsonSerializable
{
    /**
     * @var int
     */
    protected $id;
    /**
     * @var string
     */
    protected $name;
    /**
     * @var string
     */
    protected $description;
    /**
     * @var Category
     */
    protected $category;
    /**
     * @var float|null
     */
    protected $rating;

    /**
     * @param int $id
     * @param string $name
     * @param string $description
     * @param Category $category
     * @param float|null $rating
     */
    public function __construct(
        int $id,
        string $name,
        string $description,
        Category $category,
        ?float $rating
    ) {
        $this->id          = $id;
        $this->name        = $name;
        $this->description = $description;
        $this->category    = $category;
        $this->rating      = $rating;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return Category
     */
    public function getCategory(): Category
    {
        return $this->category;
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize()
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'description' => $this->description,
            'category'    => $this->category->getName(),
            'rating'      => $this->rating,
        ];
    }
}