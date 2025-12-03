<?php 
session_start();
// Vérifier si l'utilisateur est admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}
include '../Database.php';
$pdo = connectDatabase();
// Vérifier si l'id de la note est passé
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: notes.php");
    exit;
}
$note_id = (int)$_GET['id'];
// Vérifier que la note existe
$stmt = $pdo->prepare("SELECT * FROM notes WHERE id = ?");
$stmt->execute([$note_id]);
$note = $stmt->fetch();

if (!$note) {
    header("Location: notes.php");
    exit;
}
// Supprimer la note
try {
    $stmt = $pdo->prepare("DELETE FROM notes WHERE id = ?");
    $stmt->execute([$note_id]);

    // Redirection avec message
    $_SESSION['message'] = '<div class="alert alert-success">Note supprimée avec succès !</div>';
    header("Location: notes.php");
    exit;
} catch (PDOException $e) {
    $_SESSION['message'] = '<div class="alert alert-danger">Erreur lors de la suppression : ' . $e->getMessage() . '</div>';
    header("Location: notes.php");
    exit;
}
?>