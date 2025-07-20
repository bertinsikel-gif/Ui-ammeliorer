<?php
/**
 * API Endpoint pour la gestion des étudiants
 */

require_once '../config/cors.php';
require_once '../config/database.php';
require_once '../models/Student.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    $student = new Student($db);
    
    $method = $_SERVER['REQUEST_METHOD'];
    
    switch ($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                $result = $student->getById($_GET['id']);
                if ($result) {
                    echo json_encode(['success' => true, 'student' => $result]);
                } else {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'Étudiant non trouvé']);
                }
            } elseif (isset($_GET['search'])) {
                $results = $student->search($_GET['search']);
                echo json_encode(['success' => true, 'students' => $results]);
            } else {
                $results = $student->getAll();
                echo json_encode(['success' => true, 'students' => $results]);
            }
            break;
            
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!$data) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Données JSON invalides']);
                break;
            }
            
            // Validation
            if (empty($data['matricule']) || empty($data['nom']) || empty($data['prenom'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Matricule, nom et prénom sont requis']);
                break;
            }
            
            if (!$student->isValidMatricule($data['matricule'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Format de matricule invalide']);
                break;
            }
            
            // Vérifier l'unicité du matricule
            if ($student->getByMatricule($data['matricule'])) {
                http_response_code(409);
                echo json_encode(['success' => false, 'message' => 'Un étudiant avec ce matricule existe déjà']);
                break;
            }
            
            $id = $student->create($data);
            if ($id) {
                echo json_encode(['success' => true, 'message' => 'Étudiant créé avec succès', 'id' => $id]);
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
            
            // Vérifier l'existence
            if (!$student->getById($data['id'])) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Étudiant non trouvé']);
                break;
            }
            
            // Vérifier l'unicité du matricule (sauf pour l'étudiant actuel)
            $existing = $student->getByMatricule($data['matricule']);
            if ($existing && $existing['id'] != $data['id']) {
                http_response_code(409);
                echo json_encode(['success' => false, 'message' => 'Un autre étudiant avec ce matricule existe déjà']);
                break;
            }
            
            if ($student->update($data['id'], $data)) {
                echo json_encode(['success' => true, 'message' => 'Étudiant mis à jour avec succès']);
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
            
            if ($student->delete($data['id'])) {
                echo json_encode(['success' => true, 'message' => 'Étudiant supprimé avec succès']);
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