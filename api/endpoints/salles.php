<?php
/**
 * API Endpoint pour la gestion des salles
 */

require_once '../config/cors.php';
require_once '../config/database.php';
require_once '../models/Salle.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    $salle = new Salle($db);
    
    $method = $_SERVER['REQUEST_METHOD'];
    
    switch ($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                $result = $salle->getById($_GET['id']);
                if ($result) {
                    echo json_encode(['success' => true, 'salle' => $result]);
                } else {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'Salle non trouvée']);
                }
            } else {
                $results = $salle->getAll();
                echo json_encode(['success' => true, 'salles' => $results]);
            }
            break;
            
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!$data) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Données JSON invalides']);
                break;
            }
            
            if (empty($data['nom_salle'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Le nom de la salle est requis']);
                break;
            }
            
            $id = $salle->create($data);
            if ($id) {
                echo json_encode(['success' => true, 'message' => 'Salle créée avec succès', 'id' => $id]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Erreur lors de la création']);
            }
            break;
            
        case 'PUT':
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!$data || empty($data['id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'ID requis']);
                break;
            }
            
            if (!$salle->getById($data['id'])) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Salle non trouvée']);
                break;
            }
            
            if ($salle->update($data['id'], $data)) {
                echo json_encode(['success' => true, 'message' => 'Salle mise à jour avec succès']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour']);
            }
            break;
            
        case 'DELETE':
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!$data || empty($data['id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'ID requis']);
                break;
            }
            
            // Vérifier les autorisations actives
            if ($salle->hasActiveAuthorizations($data['id'])) {
                http_response_code(409);
                echo json_encode(['success' => false, 'message' => 'Impossible de supprimer cette salle car elle a des autorisations actives']);
                break;
            }
            
            if ($salle->delete($data['id'])) {
                echo json_encode(['success' => true, 'message' => 'Salle supprimée avec succès']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Erreur lors de la suppression']);
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