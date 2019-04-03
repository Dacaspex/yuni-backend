<?php

namespace App\Storage;

use App\Models\Availability;
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
     * @param int $minutes
     * @return Canteen
     * @throws NotFoundException
     */
    public function getCanteen(int $id, int $minutes): Canteen
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
                $this->getBusyness($id, $minutes),
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
     * @throws PDOException
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
     * @param int $minutes
     * @return int
     * @throws PDOException
     */
    public function getBusyness(int $canteenId, int $minutes): int
    {
        $statement = $this->pdo->prepare(
            "
                SELECT COUNT(id) AS count
                FROM busyness
                WHERE canteen_id = :canteenId
                AND created_at BETWEEN DATE_SUB(NOW(), INTERVAL :minutes MINUTE) AND NOW()
            "
        );
        $statement->bindValue(':canteenId', $canteenId, PDO::PARAM_INT);
        $statement->bindValue(':minutes', $minutes, PDO::PARAM_INT);
        $statement->execute();

        if ($record = $statement->fetch(PDO::FETCH_ASSOC)) {
            return $record['count'];
        }

        return 0;
    }

    /**
     * @param int $id
     * @return float|null
     * @throws PDOException
     */
    public function getMenuItemRating(int $id): ?float
    {
        $statement = $this->pdo->prepare(
            "
                SELECT AVG(rating) AS rating 
                FROM menu_item_reviews
                WHERE menu_item_id = :id
            "
        );
        $statement->bindValue(':id', $id, PDO::PARAM_INT);
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
                      map.schedule,
                      map.availability
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
                    $this->getMenuItemRating($record['id']),
                    $record['menu_id'],
                    Schedule::fromBitMask($record['schedule']),
                    Availability::byName($record['availability'])
                );
            }

            return $menuItems;
        } catch (PDOException | InvalidInstance $e) {
            // TODO
            throw new \RuntimeException($e->getMessage(), $e);
        }
    }

    /**
     * @param int $minutes
     * @return Canteen[]
     */
    public function getCanteens(int $minutes): array
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
                    $this->getBusyness($record['id'], $minutes),
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
                    Category::byName($record['category']),
                    $this->getMenuItemRating($record['id'])
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
                    SELECT 
                      m.menu_item_id, 
                      m.id AS menu_id, 
                      m.canteen_id, 
                      m.schedule, 
                      i.name, 
                      i.description, 
                      i.category,
                      m.availability
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
                $this->getMenuItemRating($record['menu_item_id']),
                $record['menu_id'],
                Schedule::fromBitMask($record['schedule']),
                Availability::byName($record['availability'])
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
     * @param string $building
     * @param float $longitude
     * @param float $latitude
     * @param OperatingTimes $operatingTimes
     */
    public function createCanteen(
        string $name,
        string $description,
        string $building,
        float $longitude,
        float $latitude,
        OperatingTimes $operatingTimes
    ): void {
        try {
            $statement = $this->pdo->prepare(
                "
                    INSERT INTO canteens  
                    (name, description, building, latitude, longitude) 
                    VALUES (:name, :description, :building, :latitude, :longitude)
                "
            );
            $statement->bindValue(':name', $name);
            $statement->bindValue(':description', $description);
            $statement->bindValue(':building', $building);
            $statement->bindValue(':latitude', $latitude);
            $statement->bindValue(':longitude', $longitude);
            $statement->execute();

            $this->createOperatingTimes($this->pdo->lastInsertId(), $operatingTimes);
        } catch (PDOException $e) {
            // TODO
            throw new \RuntimeException($e->getMessage(), 0, $e);
        }
    }

    /**
     * @param int $canteenId
     * @param OperatingTimes $operatingTimes
     * @throws PDOException
     */
    public function createOperatingTimes(int $canteenId, OperatingTimes $operatingTimes): void
    {
        $statement = $this->pdo->prepare(
            "
                    INSERT INTO operating_times
                    (canteen_id, day, opening_time, closing_time)
                    VALUES (:canteenId, :day, :openingTime, :closingTime)
                "
        );

        foreach ($operatingTimes->getEntries() as $entry) {
            $statement->bindValue(':canteenId', $canteenId, PDO::PARAM_INT);
            $statement->bindValue(':day', $entry['day']);
            $statement->bindValue(':openingTime', $entry['opening'], PDO::PARAM_INT);
            $statement->bindValue('closingTime', $entry['closing'], PDO::PARAM_INT);
            $statement->execute();
        }
    }

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

    /**
     * @param int $canteenId
     */
    public function createBusynessEntry(int $canteenId)
    {
        try {
            $statement = $this->pdo->prepare(
                "
                    INSERT INTO busyness
                    (canteen_id) VALUES (:canteenId)
                "
            );
            $statement->bindValue(':canteenId', $canteenId, PDO::PARAM_INT);
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
     * @param string $name
     * @param string $description
     * @param OperatingTimes $operatingTimes
     */
    public function updateCanteen(
        int $canteenId,
        string $name,
        string $description,
        OperatingTimes $operatingTimes
    ): void {
        try {
            // Update canteen information
            $canteenStatement = $this->pdo->prepare(
                "
                    UPDATE canteens
                    SET
                      name = :name,
                      description = :description
                    WHERE id = :id
                "
            );
            $canteenStatement->bindValue(':name', $name);
            $canteenStatement->bindValue(':description', $description);
            $canteenStatement->bindValue(':id', $canteenId, PDO::PARAM_INT);
            $canteenStatement->execute();

            // Update operating times. First delete old values and then insert new values
            $deleteStatement = $this->pdo->prepare(
                "
                    DELETE FROM operating_times  
                    WHERE canteen_id = :canteenId
                "
            );
            $deleteStatement->bindValue(':canteenId', $canteenId, PDO::PARAM_INT);
            $deleteStatement->execute();

            $this->createOperatingTimes($canteenId, $operatingTimes);
        } catch (PDOException $e) {
            // TODO
            throw new \RuntimeException('Could not update canteen', 0, $e);
        }
    }

    /**
     * @param int $menuItemId
     * @param string $name
     * @param string $description
     * @param Category $category
     */
    public function updateMenuItem(int $menuItemId, string $name, string $description, Category $category): void
    {
        try {
            $statement = $this->pdo->prepare(
                "
                    UPDATE menu_items
                    SET name = :name, description = :description, category = :category
                    WHERE id = :id
                "
            );
            $statement->bindValue(':name', $name);
            $statement->bindValue(':description', $description);
            $statement->bindValue(':category', $category->getName());
            $statement->bindValue(':id', $menuItemId, PDO::PARAM_INT);
            $statement->execute();
        } catch (PDOException $e) {
            // TODO
            throw new \RuntimeException($e->getMessage(), 0, $e);
        }
    }

    /**
     * @param int $menuId
     * @param string $schedule
     */
    public function updateMenuItemSchedule(int $menuId, string $schedule)
    {
        try {
            // TODO: 404
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

    /**
     * @param int $menuId
     * @param Availability $availability
     */
    public function updateMenuItemAvailability(int $menuId, Availability $availability): void
    {
        try {
            // TODO: 404
            $statement = $this->pdo->prepare(
                "
                    UPDATE map_canteen_menu_item
                    SET availability = :availability
                    WHERE id = :id
                "
            );
            $statement->bindValue(':availability', $availability->getName());
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
     * @param int $id
     */
    public function removeMenuItem(int $id): void
    {
        try {
            // Canteen menu item map
            $mapStatement = $this->pdo->prepare(
                "
                    DELETE FROM map_canteen_menu_item
                    WHERE menu_item_id = :id
                "
            );
            $mapStatement->bindValue(':id', $id, PDO::PARAM_INT);
            $mapStatement->execute();

            // Menu item reviews
            $reviewStatement = $this->pdo->prepare(
                "
                    DELETE FROM menu_item_reviews  
                    WHERE menu_item_id = :id
                "
            );
            $reviewStatement->bindValue(':id', $id, PDO::PARAM_INT);
            $reviewStatement->execute();

            // Menu item
            $statement = $this->pdo->prepare(
                "
                    DELETE FROM menu_items
                    WHERE id = :id
                "
            );
            $statement->bindValue(':id', $id, PDO::PARAM_INT);
            $statement->execute();
        } catch (PDOException $e) {
            // TODO
            throw new \RuntimeException($e->getMessage(), 0, $e);
        }
    }

    /**
     * Truncates all tables
     */
    public function truncateAll(): void
    {
        try {
            $this->pdo->query(
                "
                    TRUNCATE canteens;
                    TRUNCATE menu_items;
                    TRUNCATE map_canteen_menu_item;
                    TRUNCATE canteen_reviews;
                    TRUNCATE menu_item_reviews;
                    TRUNCATE busyness;
                "
            );
        } catch (PDOException $e) {
            // TODO
            throw new \RuntimeException($e->getMessage(), 0, $e);
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