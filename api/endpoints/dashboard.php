<?php
/**
 * API Endpoint pour les données du tableau de bord
 */

require_once '../config/cors.php';
require_once '../config/database.php';
require_once '../models/Student.php';
require_once '../models/Salle.php';
require_once '../models/Autorisation.php';
require_once '../models/HistoriqueAcces.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    $method = $_SERVER['REQUEST_METHOD'];
    
    if ($method === 'GET') {
        // Statistiques générales
        $stats = [];
        
        // Nombre d'étudiants
        $query = "SELECT COUNT(*) as total FROM etudiants WHERE actif = 1";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $stats['etudiants'] = $stmt->fetch()['total'];
        
        // Nombre de salles
        $query = "SELECT COUNT(*) as total FROM salles WHERE actif = 1";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $stats['salles'] = $stmt->fetch()['total'];
        
        // Nombre d'autorisations actives
        $query = "SELECT COUNT(*) as total FROM autorisations WHERE actif = 1";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $stats['autorisations'] = $stmt->fetch()['total'];
        
        // Accès aujourd'hui
        $query = "SELECT COUNT(*) as total FROM historiques_acces WHERE DATE(date_entree) = CURDATE()";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $stats['acces_aujourdhui'] = $stmt->fetch()['total'];
        
        // Derniers accès
        $historique = new HistoriqueAcces($db);
        $derniers_acces = $historique->getAll(10);
        
        // Autorisations récentes
        $query = "SELECT a.*, e.matricule, e.nom, e.prenom, s.nom_salle 
                  FROM autorisations a
                  JOIN etudiants e ON a.etudiant_id = e.id
                  JOIN salles s ON a.salle_id = s.id
                  WHERE a.actif = 1
                  ORDER BY a.date_creation DESC
                  LIMIT 5";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $autorisations_recentes = $stmt->fetchAll();
        
        echo json_encode([
            'success' => true,
            'stats' => $stats,
            'derniers_acces' => $derniers_acces,
            'autorisations_recentes' => $autorisations_recentes
        ]);
    } else {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>