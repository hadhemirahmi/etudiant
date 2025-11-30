<?php
session_start();

// Vérifier que l'utilisateur est admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

include '../Database.php';
$pdo = connectDatabase();

// Vérifier si l'id du teacher est passé
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: enseignants.php");
    exit;
}

$teacher_id = (int)$_GET['id'];

// Vérifier que l'enseignant existe
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role = 'enseignant'");
$stmt->execute([$teacher_id]);
$teacher = $stmt->fetch();

if (!$teacher) {
    header("Location: enseignants.php");
    exit;
}

try {
    // Supprimer l'enseignant (supprime aussi la ligne dans teachers grâce à la FK)
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$teacher_id]);

    // Redirection vers la liste avec message de succès
    header("Location: enseignants.php?msg=Enseignant supprimé avec succès");
    exit;

} catch (PDOException $e) {
    // En cas d'erreur
    echo "Erreur : " . $e->getMessage();
}
?>
