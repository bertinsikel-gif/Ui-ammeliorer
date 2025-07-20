<?php
/**
 * API Endpoint pour la gestion des autorisations
 */

require_once '../config/cors.php';
require_once '../config/database.php';
require_once '../models/Autorisation.php';
require_once '../models/Student.php';
require_once '../models/Salle.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    $autorisation = new Autorisation($db);
    $student = new Student($db);
    $salle = new Salle($db);
    
    $method = $_SERVER['REQUEST_METHOD'];
    
    switch ($method) {
        case 'GET':
            $results = $autorisation->getAll();
            echo json_encode(['success' => true, 'autorisations' => $results]);
            break;
            
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!$data) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Données JSON invalides']);
                break;
            }
            
            $type = $data['type'] ?? 'individual';
            
            if ($type === 'individual') {
                // Attribution individuelle
                $required = ['etudiant_id', 'salle_id', 'date_debut', 'date_fin'];
                foreach ($required as $field) {
                    if (empty($data[$field])) {
                        http_response_code(400);
                        echo json_encode(['success' => false, 'message' => "Le champ '$field' est requis"]);
                        exit;
                    }
                }
                
                // Validation des dates
                if (strtotime($data['date_fin']) <= strtotime($data['date_debut'])) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'La date de fin doit être postérieure à la date de début']);
                    break;
                }
                
                // Vérifier l'existence de l'étudiant et de la salle
                if (!$student->getById($data['etudiant_id'])) {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'Étudiant introuvable']);
                    break;
                }
                
                if (!$salle->getById($data['salle_id'])) {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'Salle introuvable']);
                    break;
                }
                
                $id = $autorisation->create($data);
                if ($id) {
                    echo json_encode(['success' => true, 'message' => 'Autorisation créée avec succès', 'id' => $id]);
                } else {
                    http_response_code(500);
                    echo json_encode(['success' => false, 'message' => 'Erreur lors de la création']);
                }
                
            } elseif ($type === 'group') {
                // Attribution groupée
                $required = ['faculte', 'promotion', 'salle_id', 'date_debut', 'date_fin'];
                foreach ($required as $field) {
                    if (empty($data[$field])) {
                        http_response_code(400);
                        echo json_encode(['success' => false, 'message' => "Le champ '$field' est requis"]);
                        exit;
                    }
                }
                
                // Validation des dates
                if (strtotime($data['date_fin']) <= strtotime($data['date_debut'])) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'La date de fin doit être postérieure à la date de début']);
                    break;
                }
                
                // Vérifier l'existence de la salle
                if (!$salle->getById($data['salle_id'])) {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'Salle introuvable']);
                    break;
                }
                
                $count = $autorisation->createGroupAuthorization(
                    $data['faculte'],
                    $data['promotion'],
                    $data['salle_id'],
                    $data['date_debut'],
                    $data['date_fin']
                );
                
                if ($count > 0) {
                    echo json_encode(['success' => true, 'message' => "Attribution groupée réussie pour $count étudiant(s)", 'count' => $count]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Aucun étudiant trouvé pour cette faculté/promotion']);
                }
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Type d\'autorisation non supporté']);
            }
            break;
            
        case 'DELETE':
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!$data || empty($data['id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'ID requis']);
                break;
            }
            
            if ($autorisation->revoke($data['id'])) {
                echo json_encode(['success' => true, 'message' => 'Autorisation révoquée avec succès']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Erreur lors de la révocation']);
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