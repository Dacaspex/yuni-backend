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
                    Schedule::fromDatabase($record['schedule'])
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
}