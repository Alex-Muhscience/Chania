<?php

namespace App\Core;

/**
 * Database wrapper for the App namespace
 */
class Database
{
    private $connection;

    public function connect()
    {
        if (!$this->connection) {
            // Use the existing Database class from shared
            $sharedDb = new \Database();
            $this->connection = $sharedDb->connect();
        }
        return $this->connection;
    }

    public function getConnection()
    {
        return $this->connect();
    }
}
