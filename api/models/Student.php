<?php
/**
 * Modèle Student pour la gestion des étudiants
 */

class Student {
    private $conn;
    private $table = 'etudiants';

    public function __construct($db) {
        $this->conn = $db;
    }

    // Obtenir tous les étudiants
    public function getAll() {
        $query = "SELECT * FROM " . $this->table . " WHERE actif = 1 ORDER BY nom, prenom";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Obtenir un étudiant par ID
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = ? AND actif = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // Obtenir un étudiant par matricule
    public function getByMatricule($matricule) {
        $query = "SELECT * FROM " . $this->table . " WHERE matricule = ? AND actif = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$matricule]);
        return $stmt->fetch();
    }

    // Créer un nouvel étudiant
    public function create($data) {
        $query = "INSERT INTO " . $this->table . " 
                  (matricule, nom, prenom, email, faculte, promotion, uid_firebase) 
                  VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        $result = $stmt->execute([
            $data['matricule'],
            $data['nom'],
            $data['prenom'],
            $data['email'] ?? null,
            $data['faculte'] ?? null,
            $data['promotion'] ?? null,
            $data['uid_firebase'] ?? null
        ]);

        if ($result) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    // Mettre à jour un étudiant
    public function update($id, $data) {
        $query = "UPDATE " . $this->table . " 
                  SET matricule = ?, nom = ?, prenom = ?, email = ?, 
                      faculte = ?, promotion = ?, uid_firebase = ?
                  WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            $data['matricule'],
            $data['nom'],
            $data['prenom'],
            $data['email'] ?? null,
            $data['faculte'] ?? null,
            $data['promotion'] ?? null,
            $data['uid_firebase'] ?? null,
            $id
        ]);
    }

    // Supprimer un étudiant (soft delete)
    public function delete($id) {
        $query = "UPDATE " . $this->table . " SET actif = 0 WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id]);
    }

    // Rechercher des étudiants
    public function search($term) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE actif = 1 AND (
                      matricule LIKE ? OR 
                      nom LIKE ? OR 
                      prenom LIKE ? OR 
                      email LIKE ?
                  ) ORDER BY nom, prenom";
        
        $searchTerm = "%$term%";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        return $stmt->fetchAll();
    }

    // Valider le format du matricule
    public function isValidMatricule($matricule) {
        return preg_match('/^\d{2}\/\d{2}\.\d{5}$/', $matricule);
    }
}
?>