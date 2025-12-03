<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

include '../Database.php';
$pdo = connectDatabase();

if (isset($_GET['id'])) {
    $course_id = (int)$_GET['id'];
    
    try {
        // Vérifier d'abord si le cours existe
        $stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
        $stmt->execute([$course_id]);
        $course = $stmt->fetch();
        
        if ($course) {
            // Supprimer le cours
            $stmt = $pdo->prepare("DELETE FROM courses WHERE id = ?");
            $stmt->execute([$course_id]);
            
            $_SESSION['message'] = '<div class="alert alert-success">Cours supprimé avec succès !</div>';
        } else {
            $_SESSION['message'] = '<div class="alert alert-warning">Cours non trouvé.</div>';
        }
    } catch (PDOException $e) {
        // Si des contraintes de clé étrangère empêchent la suppression
        if ($e->getCode() == 23000) {
            $_SESSION['message'] = '<div class="alert alert-danger">Impossible de supprimer ce cours : il est utilisé dans d\'autres données.</div>';
        } else {
            $_SESSION['message'] = '<div class="alert alert-danger">Erreur lors de la suppression.</div>';
        }
    }
} else {
    $_SESSION['message'] = '<div class="alert alert-warning">Aucun cours spécifié.</div>';
}

// Rediriger vers la page des cours
header('Location: cours.php');
exit;
?>