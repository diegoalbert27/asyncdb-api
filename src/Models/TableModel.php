<?php

namespace App\Models;

use App\Libraries\Database;

class tableModel
{
    public function findName(string $table_name) {
        try {
            $conn = Database::getInstance();
        
            $stmt = $conn->prepare('SELECT * FROM ' . $table_name);

            $stmt->execute();
        
            $tables = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            return $tables;
        } catch (\PDOException $e) {
            die("DataBase Error: The Customer could not be found.<br>{$e->getMessage()}");
        } finally {
            $conn = null;
        }
    }
}