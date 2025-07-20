<?php
/**
 * API d'authentification
 * SmartAccess UCB - Université Catholique de Bukavu
 */

require_once '../config/cors.php';
require_once '../config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    $method = $_SERVER['REQUEST_METHOD'];
    
    switch ($method) {
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!$data) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Données JSON invalides']);
                break;
            }
            
            $username = $data['username'] ?? '';
            $password = $data['password'] ?? '';
            
            if (empty($username) || empty($password)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Nom d\'utilisateur et mot de passe requis']);
                break;
            }
            
            // Recherche de l'administrateur
            $query = "SELECT * FROM admins WHERE username = ? AND actif = 1";
            $stmt = $db->prepare($query);
            $stmt->execute([$username]);
            $admin = $stmt->fetch();
            
            if ($admin && $password === $admin['password']) {
                // Connexion réussie
                $userData = [
                    'id' => $admin['id'],
                    'username' => $admin['username'],
                    'nom' => $admin['nom'],
                    'prenom' => $admin['prenom'],
                    'email' => $admin['email']
                ];
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Connexion réussie',
                    'user' => $userData
                ]);
            } else {
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'message' => 'Identifiants incorrects'
                ]);
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