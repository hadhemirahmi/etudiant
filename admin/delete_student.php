<?php
session_start();

// Vérifier si l'utilisateur est admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

include '../Database.php';
$pdo = connectDatabase();

// Vérifier si l'id de l'étudiant est passé
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: etudiants.php");
    exit;
}

$student_id = (int)$_GET['id'];

// Vérifier que l'étudiant existe et que c'est bien un role 'etudiant'
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role = 'etudiant'");
$stmt->execute([$student_id]);
$student = $stmt->fetch();

if (!$student) {
    header("Location: etudiants.php");
    exit;
}

// Supprimer l'étudiant
try {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$student_id]);

    // Redirection avec message
    $_SESSION['message'] = "Étudiant '{$student['name']}' supprimé avec succès !";
    header("Location: etudiants.php");
    exit;
} catch (PDOException $e) {
    $_SESSION['message'] = "Erreur lors de la suppression : " . $e->getMessage();
    header("Location: etudiants.php");
    exit;
}
