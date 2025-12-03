<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

include "../Database.php";
$pdo = connectDatabase();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID étudiant invalide.");
}

$student_id = (int)$_GET['id'];
$stmt = $pdo->prepare("
    SELECT id FROM users 
    WHERE id = ? AND role = 'etudiant'
");
$stmt->execute([$student_id]);
$exists = $stmt->fetch();

if (!$exists) {
    die("Erreur : étudiant introuvable.");
}

try {
    $pdo->beginTransaction();

    $pdo->prepare("DELETE FROM enrollments WHERE student_id = ?")
        ->execute([$student_id]);

    $pdo->prepare("DELETE FROM attendance WHERE student_id = ?")
        ->execute([$student_id]);


    $pdo->prepare("DELETE FROM grades WHERE student_id = ?")
        ->execute([$student_id]);

    $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'etudiant'")
        ->execute([$student_id]);

    $pdo->commit();

    header("Location: etudiants.php?deleted=1");
    exit;

} catch (PDOException $e) {
    $pdo->rollBack();
    die("Erreur lors de la suppression : " . htmlspecialchars($e->getMessage()));
}
?>
