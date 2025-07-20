<?php
/**
 * Modèle Salle pour la gestion des salles
 */

class Salle {
    private $conn;
    private $table = 'salles';

    public function __construct($db) {
        $this->conn = $db;
    }

    // Obtenir toutes les salles
    public function getAll() {
        $query = "SELECT * FROM " . $this->table . " WHERE actif = 1 ORDER BY nom_salle";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Obtenir une salle par ID
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = ? AND actif = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // Créer une nouvelle salle
    public function create($data) {
        $query = "INSERT INTO " . $this->table . " 
                  (nom_salle, localisation, description, capacite) 
                  VALUES (?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        $result = $stmt->execute([
            $data['nom_salle'],
            $data['localisation'] ?? null,
            $data['description'] ?? null,
            $data['capacite'] ?? null
        ]);

        if ($result) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    // Mettre à jour une salle
    public function update($id, $data) {
        $query = "UPDATE " . $this->table . " 
                  SET nom_salle = ?, localisation = ?, description = ?, capacite = ?
                  WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            $data['nom_salle'],
            $data['localisation'] ?? null,
            $data['description'] ?? null,
            $data['capacite'] ?? null,
            $id
        ]);
    }

    // Supprimer une salle (soft delete)
    public function delete($id) {
        $query = "UPDATE " . $this->table . " SET actif = 0 WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id]);
    }

    // Vérifier si une salle a des autorisations actives
    public function hasActiveAuthorizations($id) {
        $query = "SELECT COUNT(*) as count FROM autorisations 
                  WHERE salle_id = ? AND actif = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }
}
?>