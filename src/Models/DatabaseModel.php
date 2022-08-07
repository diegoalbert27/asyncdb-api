<?php

namespace App\Models;

use App\Libraries\Database;

class DatabaseModel
{
    public $type = 'BASE TABLE';
    public $db = 'Cventas';

    public function findAll() {
        try {
            $conn = Database::getInstance();
        
            $stmt = $conn->prepare('SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE = :type AND TABLE_CATALOG = :db');
    
            $stmt->bindParam(':type', $this->type);
            $stmt->bindParam(':db', $this->db);
    
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