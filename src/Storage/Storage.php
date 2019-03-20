<?php

namespace App\Storage;

use App\Models\Canteen;
use App\Models\CanteenReview;
use App\Models\Category;
use App\Models\ExtendedMenuItem;
use App\Models\MenuItem;
use App\Models\MenuItemReview;
use App\Models\OperatingTimes;
use App\Models\Schedule;
use App\Storage\Exception\NotFoundException;
use Aura\Sql\ExtendedPdo;
use PDO;
use PDOException;
use Vcn\Lib\Enum\Exception\InvalidInstance;

class Storage
{
    /**
     * @var ExtendedPdo
     */
    protected $pdo;

    /**
     * @param string $dsn
     * @param string $username
     * @param string $password
     */
    public function __construct(string $dsn, string $username, string $password)
    {
        $this->pdo = new ExtendedPdo($dsn, $username, $password);
    }

    //
    // GET operations
    //

    /**
     * @param int $id
     * @return Canteen
     * @throws NotFoundException
     */
    public function getCanteen(int $id): Canteen
    {
        try {
            // Get canteen
            $statement = $this->pdo->prepare(
                "
                    SELECT id, name, description, building, longitude, latitude
                    FROM canteens
                    WHERE id = :id
                "
            );
            $statement->bindValue(':id', $id, PDO::PARAM_INT);
            $statement->execute();

            $record = $statement->fetch(PDO::FETCH_ASSOC);
            if ($record === false) {
                throw new NotFoundException();
            }

            return new Canteen(
                $record['id'],
                $record['name'],
                $record['description'],
                $record['building'],
                $record['longitude'],
                $record['latitude'],
                $this->getOperatingTimes($id),
                $this->getCanteenRating($id),
                $this->getMenuItems($id)
            );

        } catch (PDOException $e) {
            // TODO
            throw new \RuntimeException('Could not get canteen with id ' . $id, 0, $e);
        }
    }

    /**
     * @param int $canteenId
     * @return OperatingTimes
     */
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

    /**
     * @param int $canteenId
     * @return float|null
     */
    public function getCanteenRating(int $canteenId): ?float
    {
        // Get rating
        $statement = $this->pdo->prepare(
            "
                    SELECT AVG(rating) as rating
                    FROM canteen_reviews
                    WHERE canteen_id = :id
                "
        );
        $statement->bindValue(':id', $canteenId, PDO::PARAM_INT);
        $statement->execute();

        $record = $statement->fetch(PDO::FETCH_ASSOC);

        if ($record === false) {
            return null;
        }

        return $record['rating'];
    }

    /**
     * @param int $canteenId
     * @return ExtendedMenuItem[]
     */
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
                $canteens[] = new Canteen(
                    $record['id'],
                    $record['name'],
                    $record['description'],
                    $record['building'],
                    $record['longitude'],
                    $record['latitude'],
                    $this->getOperatingTimes($record['id']),
                    $this->getCanteenRating($record['id']),
                    $this->getMenuItems($record['id'])
                );
            }

            return $canteens;
        } catch (PDOException $e) {
            // TODO
            throw new \RuntimeException($e->getMessage(), $e);
        }
    }

    /**
     * @return MenuItem[]
     */
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

    /**
     * @param int $menuId
     * @return ExtendedMenuItem
     * @throws NotFoundException
     */
    public function getItemOnMenu(int $menuId): ExtendedMenuItem
    {
        try {
            $statement = $this->pdo->prepare(
                "
                    SELECT m.menu_item_id, m.id AS menu_id, m.canteen_id, m.schedule, i.name, i.description, i.category
                    FROM map_canteen_menu_item AS m
                    LEFT JOIN menu_items AS i
                    ON m.menu_item_id = i.id
                    WHERE m.id = :menuId
                "
            );
            $statement->bindValue(':menuId', $menuId, PDO::PARAM_INT);
            $statement->execute();

            $record = $statement->fetch(PDO::FETCH_ASSOC);
            if ($record === false) {
                throw new NotFoundException();
            }

            return new ExtendedMenuItem(
                $record['menu_item_id'],
                $record['name'],
                $record['description'],
                Category::byName($record['category']),
                $record['menu_id'],
                Schedule::fromBitMask($record['schedule'])
            );
        } catch (PDOException | InvalidInstance $e) {
            throw new \RuntimeException('Could not get menu item', 0, $e);
        }
    }

    /**
     * @param int $canteenId
     * @return CanteenReview[]
     */
    public function getCanteenReviews(int $canteenId): array
    {
        try {
            $statement = $this->pdo->prepare(
                "
                    SELECT id, canteen_id, rating, description, created_at
                    FROM canteen_reviews
                    WHERE canteen_id = :id
                "
            );
            $statement->bindValue(':id', $canteenId, PDO::PARAM_INT);
            $statement->execute();

            $reviews = [];
            while (($record = $statement->fetch(PDO::FETCH_ASSOC)) !== false) {
                $reviews[] = new CanteenReview(
                    $record['id'],
                    $record['rating'],
                    $record['description'],
                    $this->parseDate($record['created_at']),
                    $record['canteen_id']
                );
            }

            return $reviews;
        } catch (PDOException $e) {
            // TODO
            throw new \RuntimeException('Could not fetch canteen reviews', 0, $e);
        }
    }

    /**
     * @param int $menuItemId
     * @return MenuItemReview[]
     */
    public function getMenuItemReviews(int $menuItemId): array
    {
        try {
            $statement = $this->pdo->prepare(
                "
                    SELECT id, menu_item_id, description, rating, created_at
                    FROM menu_item_reviews
                    WHERE menu_item_id = :id
                "
            );
            $statement->bindValue(':id', $menuItemId, PDO::PARAM_INT);
            $statement->execute();

            $reviews = [];
            while (($record = $statement->fetch(PDO::FETCH_ASSOC)) !== false) {
                $reviews[] = new MenuItemReview(
                    $record['id'],
                    $record['rating'],
                    $record['description'],
                    $this->parseDate($record['created_at']),
                    $record['menu_item_id']
                );
            }

            return $reviews;
        } catch (PDOException $e) {
            throw new \RuntimeException('Could not get reviews', 0, $e);
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

    /**
     * @param int $menuItemId
     * @param float $rating
     * @param string $description
     */
    public function createMenuItemReview(int $menuItemId, float $rating, string $description): void
    {
        try {
            $statement = $this->pdo->prepare(
                "
                    INSERT INTO menu_item_reviews
                    (menu_item_id, description, rating)
                    VALUES (:id, :description, :rating)
                "
            );
            $statement->bindValue(':id', $menuItemId, PDO::PARAM_INT);
            $statement->bindValue(':description', $description);
            $statement->bindValue(':rating', $rating);
            $statement->execute();
        } catch (PDOException $e) {
            // TODO
            throw new \RuntimeException($e->getMessage(), 0, $e);
        }
    }

    /**
     * @param int $canteenId
     * @param float $rating
     * @param string $description
     */
    public function createCanteenReview(int $canteenId, float $rating, string $description): void
    {
        try {
            $statement = $this->pdo->prepare(
                "
                    INSERT INTO canteen_reviews
                    (canteen_id, description, rating)
                    VALUES (:id, :description, :rating)
                "
            );
            $statement->bindValue(':id', $canteenId, PDO::PARAM_INT);
            $statement->bindValue(':description', $description);
            $statement->bindValue(':rating', $rating);
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

    /**
     * @param int $menuId
     * @param Schedule $schedule
     */
    public function updateSchedule(int $menuId, Schedule $schedule)
    {
        try {
            $statement = $this->pdo->prepare(
                "
                    UPDATE map_canteen_menu_item
                    SET schedule = :schedule
                    WHERE id = :id
                "
            );
            $statement->bindValue(':schedule', $schedule);
            $statement->bindValue(':id', $menuId, PDO::PARAM_INT);
            $statement->execute();
        } catch (PDOException $e) {
            // TODO
            throw new \RuntimeException($e->getMessage(), 0, $e);
        }
    }

    //
    // DELETE operations
    //

    /**
     * @param int $menuId
     * @throws NotFoundException
     */
    public function removeItemFromMenu(int $menuId): void
    {
        try {
            // Check if the item exists
            $this->getItemOnMenu($menuId);

            $statement = $this->pdo->prepare(
                "
                    DELETE FROM map_canteen_menu_item
                    WHERE id = :id
                "
            );
            $statement->bindValue(':id', $menuId, PDO::PARAM_INT);
            $statement->execute();
        } catch (PDOException $e) {
            throw new \RuntimeException('Could not remove item from menu', 0, $e);
        }
    }

    /**
     * @param string $date
     * @return \DateTimeImmutable
     */
    private function parseDate(string $date): \DateTimeImmutable
    {
        return \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $date);
    }
}