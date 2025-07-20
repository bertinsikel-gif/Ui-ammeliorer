<?php
/**
 * API de vérification d'accès
 * Endpoint: GET /api/endpoints/verifier_acces.php?matricule=XXX&salle_id=YYY
 */

require_once '../config/cors.php';
require_once '../config/database.php';
require_once '../models/Autorisation.php';
require_once '../models/Student.php';
require_once '../models/HistoriqueAcces.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
        exit;
    }
    
    $matricule = $_GET['matricule'] ?? '';
    $salle_id = $_GET['salle_id'] ?? '';
    
    if (empty($matricule)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Le paramètre "matricule" est requis']);
        exit;
    }
    
    if (empty($salle_id) || !is_numeric($salle_id)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Le paramètre "salle_id" est requis et doit être numérique']);
        exit;
    }
    
    $database = new Database();
    $db = $database->getConnection();
    $autorisation = new Autorisation($db);
    $student = new Student($db);
    $historique = new HistoriqueAcces($db);
    
    // Validation du format du matricule
    if (!$student->isValidMatricule($matricule)) {
        $response = [
            'status' => 'ACCES REFUSE',
            'message' => 'Format de matricule invalide',
            'matricule' => $matricule,
            'salle_id' => (int)$salle_id,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        // Enregistrer dans l'historique
        $historique->create([
            'salle_id' => $salle_id,
            'matricule_utilise' => $matricule,
            'statut' => 'REFUSE'
        ]);
        
        http_response_code(403);
        echo json_encode($response);
        exit;
    }
    
    // Vérifier l'accès
    $access = $autorisation->verifyAccess($matricule, $salle_id);
    
    if ($access) {
        $response = [
            'status' => 'ACCES AUTORISE',
            'etudiant' => $access['nom'] . ' ' . $access['prenom'],
            'salle' => $access['nom_salle'],
            'matricule' => $matricule,
            'salle_id' => (int)$salle_id,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        // Enregistrer dans l'historique
        $etudiant = $student->getByMatricule($matricule);
        $historique->create([
            'etudiant_id' => $etudiant['id'],
            'salle_id' => $salle_id,
            'matricule_utilise' => $matricule,
            'statut' => 'AUTORISE'
        ]);
        
        http_response_code(200);
        echo json_encode($response);
    } else {
        $response = [
            'status' => 'ACCES REFUSE',
            'message' => 'Aucune autorisation valide trouvée',
            'matricule' => $matricule,
            'salle_id' => (int)$salle_id,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        // Enregistrer dans l'historique
        $etudiant = $student->getByMatricule($matricule);
        $historique->create([
            'etudiant_id' => $etudiant ? $etudiant['id'] : null,
            'salle_id' => $salle_id,
            'matricule_utilise' => $matricule,
            'statut' => 'REFUSE'
        ]);
        
        http_response_code(403);
        echo json_encode($response);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'ERREUR',
        'message' => $e->getMessage(),
        'matricule' => $matricule ?? null,
        'salle_id' => isset($salle_id) ? (int)$salle_id : null,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?>