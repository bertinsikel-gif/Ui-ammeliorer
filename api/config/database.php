<?php
/**
 * Configuration de la base de données MySQL
 * SmartAccess UCB - Université Catholique de Bukavu
 */

class Database {
    private $host = 'localhost';
    private $db_name = 'smartaccess_ucb';
    private $username = 'root';
    private $password = '1234';
    private $charset = 'utf8mb4';
    private $conn;

    public function getConnection() {
        $this->conn = null;
        
        try {
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=" . $this->charset;
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Erreur de connexion: " . $e->getMessage());
            throw new Exception("Erreur de connexion à la base de données");
        }
        
        return $this->conn;
    }
}
?>