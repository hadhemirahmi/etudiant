<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

include '../Database.php';
$pdo = connectDatabase();
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: etudiants.php");
    exit;
}

$student_id = (int)$_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role='etudiant'");
$stmt->execute([$student_id]);
$student = $stmt->fetch();

if (!$student) {
    header("Location: etudiants.php");
    exit;
}
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if ($name === '' || $email === '') {
        $message = "<div class='alert alert-warning'>Nom et Email sont obligatoires.</div>";
    } else {
        try {
            if ($password !== '') {
                // Mettre à jour avec mot de passe
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET name=?, email=?, password=? WHERE id=?");
                $stmt->execute([$name, $email, $hashedPassword, $student_id]);
            } else {
                // Mettre à jour sans changer le mot de passe
                $stmt = $pdo->prepare("UPDATE users SET name=?, email=? WHERE id=?");
                $stmt->execute([$name, $email, $student_id]);
            }

            $message = "<div class='alert alert-success'>Étudiant mis à jour avec succès !</div>";
            // Rafraîchir les infos après modification
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$student_id]);
            $student = $stmt->fetch();
            header("Location: etudiants.php");
            exit;
        } catch (PDOException $e) {
            $message = "<div class='alert alert-danger'>Erreur : " . $e->getMessage() . "</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Modifier Étudiant</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet"/>
</head>
<body>
<div class="container mt-5">
    <h2 class="mb-4"><i class="fa fa-user-edit text-primary"></i> Modifier Étudiant</h2>
    <?= $message ?>

    <form method="post">
        <div class="mb-3">
            <label class="form-label">Nom</label>
            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($student['name']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($student['email']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Mot de passe <small class="text-muted">(laisser vide pour ne pas changer)</small></label>
            <input type="password" name="password" class="form-control">
        </div>
        <button class="btn btn-primary"><i class="fa fa-save"></i> Enregistrer</button>
        <a href="etudiants.php" class="btn btn-secondary"><i class="fa fa-arrow-left"></i> Retour</a>
    </form>
</div>
</body>
</html>
