<?php
/**
 * Modèle Autorisation pour la gestion des accès
 */

class Autorisation {
    private $conn;
    private $table = 'autorisations';

    public function __construct($db) {
        $this->conn = $db;
    }

    // Obtenir toutes les autorisations avec détails
    public function getAll() {
        $query = "SELECT a.*, e.matricule, e.nom, e.prenom, s.nom_salle 
                  FROM " . $this->table . " a
                  JOIN etudiants e ON a.etudiant_id = e.id
                  JOIN salles s ON a.salle_id = s.id
                  WHERE a.actif = 1
                  ORDER BY a.date_creation DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Créer une autorisation
    public function create($data) {
        $query = "INSERT INTO " . $this->table . " 
                  (etudiant_id, salle_id, niveau_acces, date_debut, date_fin) 
                  VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        $result = $stmt->execute([
            $data['etudiant_id'],
            $data['salle_id'],
            $data['niveau_acces'] ?? 'LECTURE',
            $data['date_debut'],
            $data['date_fin']
        ]);

        if ($result) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    // Vérifier l'accès d'un étudiant à une salle
    public function verifyAccess($matricule, $salle_id) {
        $query = "SELECT a.*, e.nom, e.prenom, s.nom_salle
                  FROM " . $this->table . " a
                  JOIN etudiants e ON a.etudiant_id = e.id
                  JOIN salles s ON a.salle_id = s.id
                  WHERE e.matricule = ? AND a.salle_id = ? AND a.actif = 1
                  AND NOW() BETWEEN a.date_debut AND a.date_fin";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$matricule, $salle_id]);
        return $stmt->fetch();
    }

    // Révoquer une autorisation
    public function revoke($id) {
        $query = "UPDATE " . $this->table . " SET actif = 0 WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id]);
    }

    // Attribution groupée par faculté/promotion
    public function createGroupAuthorization($faculte, $promotion, $salle_id, $date_debut, $date_fin) {
        // Récupérer les étudiants de la faculté/promotion
        $query = "SELECT id FROM etudiants 
                  WHERE faculte = ? AND promotion = ? AND actif = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$faculte, $promotion]);
        $students = $stmt->fetchAll();

        $count = 0;
        foreach ($students as $student) {
            // Vérifier si l'autorisation n'existe pas déjà
            $checkQuery = "SELECT id FROM " . $this->table . " 
                          WHERE etudiant_id = ? AND salle_id = ? AND actif = 1";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->execute([$student['id'], $salle_id]);
            
            if (!$checkStmt->fetch()) {
                $this->create([
                    'etudiant_id' => $student['id'],
                    'salle_id' => $salle_id,
                    'date_debut' => $date_debut,
                    'date_fin' => $date_fin
                ]);
                $count++;
            }
        }

        return $count;
    }
}
?>