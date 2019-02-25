<?php

namespace App\Storage;

use App\Models\Canteen;
use App\Models\Category;
use App\Models\ExtendedMenuItem;
use App\Models\MenuItem;
use App\Models\OperatingTimes;
use App\Models\Schedule;
use Aura\Sql\ExtendedPdo;
use PDO;
use PDOException;
use Vcn\Lib\Enum\Exception\InvalidInstance;

class Storage
{
    protected $pdo;

    public function __construct(string $dsn, string $username, string $password)
    {
        $this->pdo = new ExtendedPdo($dsn, $username, $password);
    }

    //
    // GET operations
    //

    /**
     * @return Canteen[]
     */
    public function getCanteens(): array
    {
        try {
            $query = $this->pdo->query(
                "
                    SELECT id, name, description, building, longitude, latitude
                    FROM canteens
                "
            );

            $canteens = [];
            while (($record = $query->fetch(PDO::FETCH_ASSOC)) !== false) {
                // Get operating times and menu items
                $operatingTimes = $this->getOperatingTimes($record['id']);
                $menuItems      = $this->getMenuItems($record['id']);

                $canteens[] = new Canteen(
                    $record['id'],
                    $record['name'],
                    $record['description'],
                    $record['building'],
                    $record['longitude'],
                    $record['latitude'],
                    $operatingTimes,
                    $menuItems
                );
            }

            return $canteens;
        } catch (PDOException $e) {
            // TODO
            throw new \RuntimeException($e->getMessage(), $e);
        }
    }

    public function getOperatingTimes(int $canteenId): OperatingTimes
    {
        try {
            $statement = $this->pdo->prepare(
                "
                    SELECT day, opening_time, closing_time
                    FROM operating_times
                    WHERE canteen_id = :canteenId
                "
            );
            $statement->bindValue(':canteenId', $canteenId, PDO::PARAM_INT);
            $statement->execute();

            $openingTimes = [];
            $closingTimes = [];
            while (($record = $statement->fetch(PDO::FETCH_ASSOC)) !== false) {
                $openingTimes[$record['day']] = $record['opening_time'];
                $closingTimes[$record['day']] = $record['closing_time'];
            }

            return new OperatingTimes($openingTimes, $closingTimes);
        } catch (PDOException $e) {
            // TODO
            throw new \RuntimeException($e->getMessage(), $e);
        }
    }

    public function getMenuItems(int $canteenId): array
    {
        try {
            $statement = $this->pdo->prepare(
                "
                    SELECT 
                      item.id, 
                      map.id AS menu_id, 
                      item.name, 
                      item.description,
                      category,
                      map.schedule
                    FROM map_canteen_menu_item AS map
                    LEFT JOIN menu_items AS item
                    ON map.menu_item_id = item.id
                    WHERE map.canteen_id = :canteenId
                "
            );
            $statement->bindValue(':canteenId', $canteenId, PDO::PARAM_INT);
            $statement->execute();

            $menuItems = [];
            while (($record = $statement->fetch(PDO::FETCH_ASSOC)) !== false) {
                $menuItems[] = new ExtendedMenuItem(
                    $record['id'],
                    $record['name'],
                    $record['description'],
                    Category::byName($record['category']),
                    $record['menu_id'],
                    Schedule::fromBitMask($record['schedule'])
                );
            }

            return $menuItems;
        } catch (PDOException | InvalidInstance $e) {
            // TODO
            throw new \RuntimeException($e->getMessage(), $e);
        }
    }

    public function getAllMenuItems(): array
    {
        try {
            $statement = $this->pdo->query(
                "
                    SELECT id, name, description, category 
                    FROM menu_items
                "
            );

            $menuItems = [];
            while (($record = $statement->fetch(PDO::FETCH_ASSOC)) !== false) {
                $menuItems[] = new MenuItem(
                    $record['id'],
                    $record['name'],
                    $record['description'],
                    Category::byName($record['category'])
                );
            }

            return $menuItems;
        } catch (PDOException | InvalidInstance $e) {
            // TODO
            throw new \RuntimeException($e->getMessage(), $e);
        }
    }

    //
    // CREATE operations
    //

    /**
     * @param string $name
     * @param string $description
     * @param Category $category
     */
    public function createMenuItem(string $name, string $description, Category $category): void
    {
        try {
            $statement = $this->pdo->prepare(
                "
                    INSERT INTO menu_items
                    (name, description, category)
                    VALUES (:name, :description, :category)
                "
            );
            $statement->bindValue(':name', $name);
            $statement->bindValue(':description', $description);
            $statement->bindValue(':category', $category->getName());
            $statement->execute();
        } catch (PDOException $e) {
            // TODO
            throw new \RuntimeException($e->getMessage(), 0, $e);
        }
    }

    /**
     * @param int $canteenId
     * @param int $menuItemId
     * @param Schedule $schedule
     */
    public function addMenuItemToMenu(int $canteenId, int $menuItemId, Schedule $schedule): void
    {
        try {
            $statement = $this->pdo->prepare(
                "
                    INSERT INTO map_canteen_menu_item
                    (canteen_id, menu_item_id, schedule)
                    VALUES (:canteenId, :menuItemId, :schedule)
                "
            );
            $statement->bindValue(':canteenId', $canteenId, PDO::PARAM_INT);
            $statement->bindValue(':menuItemId', $menuItemId, PDO::PARAM_INT);
            $statement->bindValue(':schedule', $schedule->toBitMask());
            $statement->execute();
        } catch (PDOException $e) {
            // TODO
            throw new \RuntimeException($e->getMessage(), 0, $e);
        }
    }

    //
    // UPDATE operations
    //

    /**
     * @param int $canteenId
     * @param string|null $name
     * @param string|null $description
     * @param string|null $building
     * @param float|null $longitude
     * @param float|null $latitude
     */
    public function updateCanteen(
        int $canteenId,
        ?string $name,
        ?string $description,
        ?string $building,
        ?float $longitude,
        ?float $latitude
    ): void {
        try {
            $fields = [];
            if ($name !== null) {
                $fields[] = "name = :name";
            }
            if ($description !== null) {
                $fields[] = "description = :description";
            }
            if ($building !== null) {
                $fields[] = "building = :building";
            }
            if ($longitude !== null) {
                $fields[] = "longitude = :longitude";
            }
            if ($latitude !== null) {
                $fields[] = "latitude = :latitude";
            }

            $setClauses = implode(',', $fields);
            $query      = "UPDATE canteens SET {$setClauses} WHERE id = :id";
            $statement  = $this->pdo->prepare($query);
            $statement->bindValue(':id', $canteenId, PDO::PARAM_INT);

            if ($name !== null) {
                $statement->bindValue(':name', $name);
            }
            if ($description !== null) {
                $statement->bindValue(':description', $description);
            }
            if ($building !== null) {
                $statement->bindValue(':building', $building);
            }
            if ($longitude !== null) {
                $statement->bindValue(':longitude', $longitude);
            }
            if ($latitude !== null) {
                $statement->bindValue(':latitude', $latitude);
            }

            $statement->execute();

        } catch (PDOException $e) {
            // TODO
            throw new \RuntimeException($e->getMessage(), 0, $e);
        }
    }
}