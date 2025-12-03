<?php
session_start();

// Accès réservé admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

include "../Database.php";
$pdo = connectDatabase();

/* -------------------------------------------------------
   Vérification de l'ID étudiant
-------------------------------------------------------- */
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID étudiant invalide.");
}

$student_id = (int)$_GET['id'];

/* -------------------------------------------------------
   Vérifier que l’étudiant existe
-------------------------------------------------------- */
$stmt = $pdo->prepare("
    SELECT id FROM users 
    WHERE id = ? AND role = 'etudiant'
");
$stmt->execute([$student_id]);
$exists = $stmt->fetch();

if (!$exists) {
    die("Erreur : étudiant introuvable.");
}

/* -------------------------------------------------------
   Suppression propre + transaction
-------------------------------------------------------- */
try {
    $pdo->beginTransaction();

    // 1. Supprimer ses inscriptions aux cours
    $pdo->prepare("DELETE FROM enrollments WHERE student_id = ?")
        ->execute([$student_id]);

    // 2. Supprimer ses absences (si table attendance existe)
    $pdo->prepare("DELETE FROM attendance WHERE student_id = ?")
        ->execute([$student_id]);

    // 3. Supprimer ses notes (si table grades existe)
    $pdo->prepare("DELETE FROM grades WHERE student_id = ?")
        ->execute([$student_id]);

    // 4. Supprimer le compte utilisateur
    $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'etudiant'")
        ->execute([$student_id]);

    $pdo->commit();

    // Redirection
    header("Location: etudiants.php?deleted=1");
    exit;

} catch (PDOException $e) {
    $pdo->rollBack();
    die("Erreur lors de la suppression : " . htmlspecialchars($e->getMessage()));
}
?>
