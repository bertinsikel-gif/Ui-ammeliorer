<?php
/**
 * Modèle HistoriqueAcces pour l'historique des accès
 */

class HistoriqueAcces {
    private $conn;
    private $table = 'historiques_acces';

    public function __construct($db) {
        $this->conn = $db;
    }

    // Obtenir l'historique des accès
    public function getAll($limit = 100) {
        $query = "SELECT h.*, e.nom, e.prenom, s.nom_salle
                  FROM " . $this->table . " h
                  LEFT JOIN etudiants e ON h.etudiant_id = e.id
                  LEFT JOIN salles s ON h.salle_id = s.id
                  ORDER BY h.date_entree DESC
                  LIMIT ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    // Enregistrer un accès
    public function create($data) {
        $query = "INSERT INTO " . $this->table . " 
                  (etudiant_id, salle_id, matricule_utilise, type_acces, statut, 
                   date_entree, ip_address, user_agent) 
                  VALUES (?, ?, ?, ?, ?, NOW(), ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            $data['etudiant_id'] ?? null,
            $data['salle_id'],
            $data['matricule_utilise'],
            $data['type_acces'] ?? 'ENTREE',
            $data['statut'],
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);
    }

    // Statistiques des accès
    public function getStats() {
        $stats = [];
        
        // Total des accès
        $query = "SELECT COUNT(*) as total FROM " . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['total'] = $stmt->fetch()['total'];
        
        // Accès autorisés
        $query = "SELECT COUNT(*) as total FROM " . $this->table . " WHERE statut = 'AUTORISE'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['autorises'] = $stmt->fetch()['total'];
        
        // Accès refusés
        $query = "SELECT COUNT(*) as total FROM " . $this->table . " WHERE statut = 'REFUSE'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['refuses'] = $stmt->fetch()['total'];
        
        // Accès aujourd'hui
        $query = "SELECT COUNT(*) as total FROM " . $this->table . " WHERE DATE(date_entree) = CURDATE()";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['aujourdhui'] = $stmt->fetch()['total'];
        
        return $stats;
    }
}
?>