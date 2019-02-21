<?php

namespace App\Storage;

use App\Models\Canteen;
use Aura\Sql\ExtendedPdo;
use PDO;
use PDOException;

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
                    SELECT id, name, description
                    FROM canteens
                "
            );

            $canteens = [];
            while (($record = $query->fetch(PDO::FETCH_ASSOC)) !== false) {
                $canteens[] = new Canteen(
                    $record['id'],
                    $record['name'],
                    $record['description']
                );
            }

            return $canteens;
        } catch (PDOException $e) {
            // TODO
            throw new \RuntimeException($e->getMessage(), $e);
        }
    }
}