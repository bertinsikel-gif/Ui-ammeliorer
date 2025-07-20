<?php
/**
 * API Endpoint pour l'historique des accès
 */

require_once '../config/cors.php';
require_once '../config/database.php';
require_once '../models/HistoriqueAcces.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    $historique = new HistoriqueAcces($db);
    
    $method = $_SERVER['REQUEST_METHOD'];
    
    switch ($method) {
        case 'GET':
            if (isset($_GET['stats'])) {
                $stats = $historique->getStats();
                echo json_encode(['success' => true, 'stats' => $stats]);
            } else {
                $limit = $_GET['limit'] ?? 100;
                $results = $historique->getAll($limit);
                echo json_encode(['success' => true, 'historique' => $results]);
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            break;
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>