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
   Vérification de l'ID enseignant
-------------------------------------------------------- */
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID enseignant invalide.");
}

$teacher_id = (int)$_GET['id'];

/* -------------------------------------------------------
   Vérifier que l'enseignant existe
-------------------------------------------------------- */
$stmt = $pdo->prepare("
    SELECT u.id FROM users u
    WHERE u.id = ? AND u.role = 'enseignant'
");
$stmt->execute([$teacher_id]);
$exists = $stmt->fetch();

if (!$exists) {
    die("Erreur : enseignant introuvable.");
}

/* -------------------------------------------------------
   Suppression propre + transaction
-------------------------------------------------------- */
try {
    $pdo->beginTransaction();

    // 1. Supprimer ses cours assignés
    $pdo->prepare("DELETE FROM course_assignments WHERE teacher_id = ?")
        ->execute([$teacher_id]);

    // 2. Supprimer entrée dans teachers
    $pdo->prepare("DELETE FROM teachers WHERE user_id = ?")
        ->execute([$teacher_id]);

    // 3. Supprimer l’utilisateur (enseignant)
    $pdo->prepare("DELETE FROM users WHERE id = ?")
        ->execute([$teacher_id]);

    $pdo->commit();

    // Redirection après suppression
    header("Location: enseignants.php?deleted=1");
    exit;

} catch (PDOException $e) {
    $pdo->rollBack();
    die("Erreur lors de la suppression : " . htmlspecialchars($e->getMessage()));
}
?>
